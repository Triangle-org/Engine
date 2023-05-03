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

namespace support\telegram;

use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;
use support\telegram\HttpClients\GuzzleHttpClient;
use support\telegram\HttpClients\HttpClientInterface;

final class TelegramClient
{
    /**
     * @var string
     */
    public const BASE_BOT_URL = 'https://api.telegram.org/bot';

    private HttpClientInterface $httpClientHandler;

    private string $baseBotUrl;

    public function __construct(HttpClientInterface $httpClientHandler = null, string $baseBotUrl = null)
    {
        $this->httpClientHandler = $httpClientHandler ?? new GuzzleHttpClient();
        $this->baseBotUrl = $baseBotUrl ?? self::BASE_BOT_URL;
    }

    public function getHttpClientHandler(): HttpClientInterface
    {
        return $this->httpClientHandler ?? new GuzzleHttpClient();
    }

    public function setHttpClientHandler(HttpClientInterface $httpClientHandler): self
    {
        $this->httpClientHandler = $httpClientHandler;

        return $this;
    }

    public function sendRequest(TelegramRequest $request): TelegramResponse
    {
        [$url, $method, $headers, $isAsyncRequest] = $this->prepareRequest($request);
        $options = $this->getOptions($request, $method);

        $rawResponse = $this->httpClientHandler
            ->setTimeOut($request->getTimeOut())
            ->setConnectTimeOut($request->getConnectTimeOut())
            ->send($url, $method, $headers, $options, $isAsyncRequest);

        $response = $this->getResponse($request, $rawResponse);

        if ($response->isError()) {
            throw $response->getThrownException();
        }

        return $response;
    }

    public function prepareRequest(TelegramRequest $request): array
    {
        $url = $this->baseBotUrl . $request->getAccessToken() . '/' . $request->getEndpoint();

        return [$url, $request->getMethod(), $request->getHeaders(), $request->isAsyncRequest()];
    }

    public function getBaseBotUrl(): string
    {
        return $this->baseBotUrl;
    }

    private function getResponse(TelegramRequest $request, ResponseInterface|PromiseInterface|null $response): TelegramResponse
    {
        return new TelegramResponse($request, $response);
    }

    private function getOptions(TelegramRequest $request, string $method): array
    {
        return $method === 'POST' ? $request->getPostParams() : ['query' => $request->getParams()];
    }
}
