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

/**
 * FrameX Http clients interface
 */
interface HttpClientInterface
{
    /**
     * Send request to the remote server
     *
     * Returns the result (Raw response from the server) on success, FALSE on failure
     *
     * @param string $uri
     * @param string $method
     * @param array $parameters
     * @param array $headers
     * @param bool $multipart
     *
     * @return mixed
     */
    public function request(string $uri, string $method = 'GET', array $parameters = [], array $headers = [], bool $multipart = false): mixed;

    /**
     * Returns raw response from the server on success, FALSE on failure
     *
     * @return mixed
     */
    public function getResponseBody(): mixed;

    /**
     * Retriever the headers returned in the response
     *
     * @return array
     */
    public function getResponseHeader(): array;

    /**
     * Returns latest request HTTP status code
     *
     * @return int
     */
    public function getResponseHttpCode(): int;

    /**
     * Returns latest error encountered by the client
     * This can be either a code or error message
     *
     * @return mixed
     */
    public function getResponseClientError(): mixed;
}
