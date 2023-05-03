<?php

/**
 * @package     Triangle Engine (FrameX Project)
 * @link        https://github.com/localzet/FrameX      FrameX Project v1-2
 * @link        https://github.com/Triangle-org/Engine  Triangle Engine v2+
 *
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright (c) 2018-2023 Localzet Group
 * @license     https://www.gnu.org/licenses/agpl AGPL-3.0 license
 *
 *              This program is free software: you can redistribute it and/or modify
 *              it under the terms of the GNU Affero General Public License as
 *              published by the Free Software Foundation, either version 3 of the
 *              License, or (at your option) any later version.
 *
 *              This program is distributed in the hope that it will be useful,
 *              but WITHOUT ANY WARRANTY; without even the implied warranty of
 *              MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *              GNU Affero General Public License for more details.
 *
 *              You should have received a copy of the GNU Affero General Public License
 *              along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace support\jwt\lib;

use ArrayAccess;
use LogicException;
use OutOfBoundsException;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use RuntimeException;
use function is_null;
use function strlen;

/**
 * @implements ArrayAccess<string, Key>
 */
class CachedKeySet implements ArrayAccess
{
    /**
     * @var string
     */
    private string $jwksUri;
    /**
     * @var ClientInterface
     */
    private ClientInterface $httpClient;
    /**
     * @var RequestFactoryInterface
     */
    private RequestFactoryInterface $httpFactory;
    /**
     * @var CacheItemPoolInterface
     */
    private CacheItemPoolInterface $cache;
    /**
     * @var ?int
     */
    private ?int $expiresAfter;
    /**
     * @var ?CacheItemInterface
     */
    private ?CacheItemInterface $cacheItem;
    /**
     * @var array<string, Key>
     */
    private array $keySet;
    /**
     * @var string
     */
    private string $cacheKey;
    /**
     * @var string
     */
    private string $cacheKeyPrefix = 'jwks';
    /**
     * @var int
     */
    private int $maxKeyLength = 64;
    /**
     * @var bool
     */
    private bool $rateLimit;
    /**
     * @var string
     */
    private string $rateLimitCacheKey;
    /**
     * @var int
     */
    private int $maxCallsPerMinute = 10;
    /**
     * @var string|null
     */
    private ?string $defaultAlg;

    public function __construct(
        string                  $jwksUri,
        ClientInterface         $httpClient,
        RequestFactoryInterface $httpFactory,
        CacheItemPoolInterface  $cache,
        int                     $expiresAfter = null,
        bool                    $rateLimit = false,
        string                  $defaultAlg = null
    )
    {
        $this->jwksUri = $jwksUri;
        $this->httpClient = $httpClient;
        $this->httpFactory = $httpFactory;
        $this->cache = $cache;
        $this->expiresAfter = $expiresAfter;
        $this->rateLimit = $rateLimit;
        $this->defaultAlg = $defaultAlg;
        $this->setCacheKeys();
    }

    /**
     * @param string $keyId
     * @return Key
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function offsetGet($keyId): Key
    {
        if (!$this->keyIdExists($keyId)) {
            throw new OutOfBoundsException('Идентификатор ключа не найден');
        }
        return $this->keySet[$keyId];
    }

    /**
     * @param string $keyId
     * @return bool
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function offsetExists($keyId): bool
    {
        return $this->keyIdExists($keyId);
    }

    /**
     * @param string $offset
     * @param Key $value
     */
    public function offsetSet($offset, $value): void
    {
        throw new LogicException('Метод не реализован');
    }

    /**
     * @param string $offset
     */
    public function offsetUnset($offset): void
    {
        throw new LogicException('Метод не реализован');
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \Psr\Cache\InvalidArgumentException
     */
    private function keyIdExists(string $keyId): bool
    {
        if (null === $this->keySet) {
            $item = $this->getCacheItem();
            if ($item->isHit()) {
                $jwks = $item->get();
                $this->keySet = JWK::parseKeySet(json_decode($jwks, true), $this->defaultAlg);
            }
        }

        if (!isset($this->keySet[$keyId])) {
            if ($this->rateLimitExceeded()) {
                return false;
            }
            $request = $this->httpFactory->createRequest('GET', $this->jwksUri);
            $jwksResponse = $this->httpClient->sendRequest($request);
            $jwks = (string)$jwksResponse->getBody();
            $this->keySet = JWK::parseKeySet(json_decode($jwks, true), $this->defaultAlg);

            if (!isset($this->keySet[$keyId])) {
                return false;
            }

            $item = $this->getCacheItem();
            $item->set($jwks);
            if ($this->expiresAfter) {
                $item->expiresAfter($this->expiresAfter);
            }
            $this->cache->save($item);
        }

        return true;
    }

    /**
     * @throws \Psr\Cache\InvalidArgumentException
     */
    private function rateLimitExceeded(): bool
    {
        if (!$this->rateLimit) {
            return false;
        }

        $cacheItem = $this->cache->getItem($this->rateLimitCacheKey);
        if (!$cacheItem->isHit()) {
            $cacheItem->expiresAfter(1);
        }

        $callsPerMinute = (int)$cacheItem->get();
        if (++$callsPerMinute > $this->maxCallsPerMinute) {
            return true;
        }
        $cacheItem->set($callsPerMinute);
        $this->cache->save($cacheItem);
        return false;
    }

    /**
     * @throws \Psr\Cache\InvalidArgumentException
     */
    private function getCacheItem(): CacheItemInterface
    {
        if (is_null($this->cacheItem)) {
            $this->cacheItem = $this->cache->getItem($this->cacheKey);
        }

        return $this->cacheItem;
    }

    private function setCacheKeys(): void
    {
        if (empty($this->jwksUri)) {
            throw new RuntimeException('JWKS URI пуст');
        }

        $key = preg_replace('|[^a-zA-Z0-9_.!]|', '', $this->jwksUri);

        $key = $this->cacheKeyPrefix . $key;

        if (strlen($key) > $this->maxKeyLength) {
            $key = substr(hash('sha256', $key), 0, $this->maxKeyLength);
        }

        $this->cacheKey = $key;

        if ($this->rateLimit) {
            $rateLimitKey = $this->cacheKeyPrefix . 'ratelimit' . $key;

            if (strlen($rateLimitKey) > $this->maxKeyLength) {
                $rateLimitKey = substr(hash('sha256', $rateLimitKey), 0, $this->maxKeyLength);
            }

            $this->rateLimitCacheKey = $rateLimitKey;
        }
    }
}
