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

namespace support\http;

use Monolog\Logger;
use support\Log;

/**
 * FrameX default Http client
 */
class Curl implements HttpClientInterface
{
    /**
     * Default curl options
     *
     * These defaults options can be overwritten when sending requests.
     *
     * See setCurlOptions()
     *
     * @var array
     */
    protected array $curlOptions = [
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 5,
        CURLINFO_HEADER_OUT => true,
        CURLOPT_ENCODING => 'identity',
        // phpcs:ignore
        CURLOPT_USERAGENT => 'Triangle Project',
    ];

    /**
     * Method request() arguments
     *
     * This is used for debugging.
     *
     * @var array
     */
    protected array $requestArguments = [];

    /**
     * Default request headers
     *
     * @var array
     */
    protected array $requestHeader = [
        'Accept' => '*/*',
        'Content-Type' => 'application/json',
        'Cache-Control' => 'max-age=0',
        'Connection' => 'keep-alive',
        'Expect' => '',
        'Pragma' => '',
    ];

    /**
     * Raw response returned by server
     *
     * @var string
     */
    protected string $responseBody = '';

    /**
     * Headers returned in the response
     *
     * @var array
     */
    protected array $responseHeader = [];

    /**
     * Response HTTP status code
     *
     * @var int
     */
    protected int $responseHttpCode = 0;

    /**
     * Last curl error number
     *
     * @var mixed
     */
    protected mixed $responseClientError = null;

    /**
     * Information about the last transfer
     *
     * @var mixed
     */
    protected mixed $responseClientInfo = [];

    /**
     * logger instance
     *
     * @var \Monolog\Logger|Log|null
     */
    protected null|Logger|Log $logger = null;

    function __construct()
    {
        if (config('log.http', false)) {
            $this->logger = Log::channel('http');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function request(string $uri, string $method = 'GET', array $parameters = [], array $headers = [], bool $multipart = false): string|bool
    {
        $this->requestHeader = array_replace($this->requestHeader, $headers);

        $this->requestArguments = [
            'uri' => $uri,
            'method' => $method,
            'parameters' => $parameters,
            'headers' => $this->requestHeader,
        ];

        $curl = curl_init();

        switch ($method) {
            case 'GET':
            case 'DELETE':
                unset($this->curlOptions[CURLOPT_POST]);
                unset($this->curlOptions[CURLOPT_POSTFIELDS]);

                $uri = $uri . (strpos($uri, '?') ? '&' : '?') . http_build_query($parameters);
                if ($method === 'DELETE') {
                    $this->curlOptions[CURLOPT_CUSTOMREQUEST] = 'DELETE';
                }
                break;
            case 'PUT':
            case 'POST':
            case 'PATCH':
                $body_content = $multipart ? $parameters : http_build_query($parameters);
                if (
                    isset($this->requestHeader['Content-Type'])
                    && $this->requestHeader['Content-Type'] == 'application/json'
                ) {
                    $body_content = json_encode($parameters);
                }

                $this->curlOptions[CURLOPT_CUSTOMREQUEST] = $method;
                if ($method === 'POST') {
                    $this->curlOptions[CURLOPT_POST] = true;
                }
                $this->curlOptions[CURLOPT_POSTFIELDS] = $body_content;
                break;
        }

        $this->curlOptions[CURLOPT_URL] = $uri;
        $this->curlOptions[CURLOPT_HTTPHEADER] = $this->prepareRequestHeaders();
        $this->curlOptions[CURLOPT_HEADERFUNCTION] = [$this, 'fetchResponseHeader'];

        foreach ($this->curlOptions as $opt => $value) {
            curl_setopt($curl, $opt, $value);
        }

        $response = curl_exec($curl);

        $this->responseBody = $response;
        $this->responseHttpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $this->responseClientError = curl_error($curl);
        $this->responseClientInfo = curl_getinfo($curl);

        if ($this->logger) {
            // phpcs:ignore
            $this->logger->debug(sprintf('%s::request( %s, %s ), response:', get_class($this), $uri, $method), $this->getResponse());

            if (false === $response) {
                // phpcs:ignore
                $this->logger->error(sprintf('%s::request( %s, %s ), error:', get_class($this), $uri, $method), [$this->responseClientError]);
            }
        }

        curl_close($curl);

        return $this->responseBody;
    }

    /**
     * Get response details
     *
     * @return array Map structure of details
     */
    public function getResponse(): array
    {
        $curlOptions = $this->curlOptions;

        $curlOptions[CURLOPT_HEADERFUNCTION] = '*omitted';

        return [
            'request' => $this->getRequestArguments(),
            'response' => [
                'code' => $this->getResponseHttpCode(),
                'headers' => $this->getResponseHeader(),
                'body' => $this->getResponseBody(),
            ],
            'client' => [
                'error' => $this->getResponseClientError(),
                'info' => $this->getResponseClientInfo(),
                'opts' => $curlOptions,
            ],
        ];
    }

    /**
     * Reset curl options
     *
     * @param array $curlOptions
     */
    public function setCurlOptions(array $curlOptions): void
    {
        foreach ($curlOptions as $opt => $value) {
            $this->curlOptions[$opt] = $value;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getResponseBody(): string
    {
        return $this->responseBody;
    }

    /**
     * {@inheritdoc}
     */
    public function getResponseHeader(): array
    {
        return $this->responseHeader;
    }

    /**
     * {@inheritdoc}
     */
    public function getResponseHttpCode(): int
    {
        return $this->responseHttpCode;
    }

    /**
     * {@inheritdoc}
     */
    public function getResponseClientError(): mixed
    {
        return $this->responseClientError;
    }

    /**
     * @return array
     */
    protected function getResponseClientInfo(): array
    {
        return $this->responseClientInfo;
    }

    /**
     * Returns method request() arguments
     *
     * This is used for debugging.
     *
     * @return array
     */
    protected function getRequestArguments(): array
    {
        return $this->requestArguments;
    }

    /**
     * Fetch server response headers
     *
     * @param mixed $curl
     * @param string $header
     *
     * @return int
     */
    protected function fetchResponseHeader(mixed $curl, string $header): int
    {
        $pos = strpos($header, ':');

        if (!empty($pos)) {
            $key = str_replace('-', '_', strtolower(substr($header, 0, $pos)));

            $value = trim(substr($header, $pos + 2));

            $this->responseHeader[$key] = $value;
        }

        return strlen($header);
    }

    /**
     * Convert request headers to the expect curl format
     *
     * @return array
     */
    protected function prepareRequestHeaders(): array
    {
        $headers = [];

        foreach ($this->requestHeader as $header => $value) {
            $headers[] = trim($header) . ': ' . trim($value);
        }

        return $headers;
    }
}
