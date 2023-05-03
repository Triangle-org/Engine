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

namespace support\telegram\Traits;

use InvalidArgumentException;
use support\telegram\Exceptions\CouldNotUploadInputFile;
use support\telegram\Exceptions\TelegramSDKException;
use support\telegram\FileUpload\InputFile;
use support\telegram\HttpClients\HttpClientInterface;
use support\telegram\Objects\BaseObject;
use support\telegram\Objects\File;
use support\telegram\TelegramClient;
use support\telegram\TelegramRequest;
use support\telegram\TelegramResponse;

/**
 * Http.
 */
trait Http
{
    use Validator;

    /** @var string Telegram Bot API Access Token. */
    protected $accessToken = null;

    /** @var TelegramClient The Telegram client service. */
    protected $client = null;

    /** @var HttpClientInterface|null Http Client Handler */
    protected $httpClientHandler = null;

    /** @var string|null Base Bot Url */
    protected $baseBotUrl = null;

    /** @var bool Indicates if the request to Telegram will be asynchronous (non-blocking). */
    protected $isAsyncRequest = false;

    /** @var int Timeout of the request in seconds. */
    protected $timeOut = 60;

    /** @var int Connection timeout of the request in seconds. */
    protected $connectTimeOut = 10;

    /** @var TelegramResponse|null Stores the last request made to Telegram Bot API. */
    protected $lastResponse;

    /**
     * Set Http Client Handler.
     *
     * @param HttpClientInterface $httpClientHandler
     * @return $this
     */
    public function setHttpClientHandler(HttpClientInterface $httpClientHandler)
    {
        $this->httpClientHandler = $httpClientHandler;

        return $this;
    }

    /**
     * Set Http Client Handler.
     *
     * @param string $baseBotUrl
     * @return $this
     */
    public function setBaseBotUrl(string $baseBotUrl)
    {
        $this->baseBotUrl = $baseBotUrl;

        return $this;
    }

    /**
     * Returns the TelegramClient service.
     *
     * @return TelegramClient
     */
    protected function getClient(): TelegramClient
    {
        if ($this->client === null) {
            $this->client = new TelegramClient($this->httpClientHandler, $this->baseBotUrl);
        }

        return $this->client;
    }

    /**
     * Returns the last response returned from API request.
     *
     * @return TelegramResponse|null
     */
    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    /**
     * Download a file from Telegram server by file ID.
     *
     * @param File|BaseObject|string $file Telegram File Instance / File Response Object or File ID.
     * @param string $filename Absolute path to dir or filename to save as.
     *
     * @return string
     * @throws TelegramSDKException
     *
     */
    public function downloadFile($file, string $filename): string
    {
        $originalFilename = null;
        if (!$file instanceof File) {
            if ($file instanceof BaseObject) {
                $originalFilename = $file->get('file_name');

                // Try to get file_id from the object or default to the original param.
                $file = $file->get('file_id');
            }

            if (!is_string($file)) {
                throw new InvalidArgumentException(
                    'Invalid $file param provided. Please provide one of file_id, File or Response object containing file_id'
                );
            }

            $file = $this->getFile(['file_id' => $file]);
        }

        // No filename provided.
        if (pathinfo($filename, PATHINFO_EXTENSION) === '') {
            // Attempt to use the original file name if there is one or fallback to the file_path filename.
            $filename .= DIRECTORY_SEPARATOR . ($originalFilename ?: basename($file->file_path));
        }

        return $this->getClient()->download($file->file_path, $filename);
    }

    /**
     * Returns Telegram Bot API Access Token.
     *
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    /**
     * Sets the bot access token to use with API requests.
     *
     * @param string $accessToken The bot access token to save.
     *
     * @return $this
     */
    public function setAccessToken(string $accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * Check if this is an asynchronous request (non-blocking).
     *
     * @return bool
     */
    public function isAsyncRequest(): bool
    {
        return $this->isAsyncRequest;
    }

    /**
     * Make this request asynchronous (non-blocking).
     *
     * @param bool $isAsyncRequest
     *
     * @return $this
     */
    public function setAsyncRequest(bool $isAsyncRequest)
    {
        $this->isAsyncRequest = $isAsyncRequest;

        return $this;
    }

    /**
     * @return int
     */
    public function getTimeOut(): int
    {
        return $this->timeOut;
    }

    /**
     * @param int $timeOut
     *
     * @return $this
     */
    public function setTimeOut(int $timeOut)
    {
        $this->timeOut = $timeOut;

        return $this;
    }

    /**
     * @return int
     */
    public function getConnectTimeOut(): int
    {
        return $this->connectTimeOut;
    }

    /**
     * @param int $connectTimeOut
     *
     * @return $this
     */
    public function setConnectTimeOut(int $connectTimeOut)
    {
        $this->connectTimeOut = $connectTimeOut;

        return $this;
    }

    /**
     * Sends a GET request to Telegram Bot API and returns the result.
     *
     * @param string $endpoint
     * @param array $params
     *
     * @return TelegramResponse
     * @throws TelegramSDKException
     *
     */
    protected function get(string $endpoint, array $params = []): TelegramResponse
    {
        $params = $this->replyMarkupToString($params);

        return $this->sendRequest('GET', $endpoint, $params);
    }

    /**
     * Sends a POST request to Telegram Bot API and returns the result.
     *
     * @param string $endpoint
     * @param array $params
     * @param bool $fileUpload Set true if a file is being uploaded.
     *
     * @return TelegramResponse
     * @throws TelegramSDKException
     */
    protected function post(string $endpoint, array $params = [], $fileUpload = false): TelegramResponse
    {
        $params = $this->normalizeParams($params, $fileUpload);

        return $this->sendRequest('POST', $endpoint, $params);
    }

    /**
     * Converts a reply_markup field in the $params to a string.
     *
     * @param array $params
     *
     * @return array
     */
    protected function replyMarkupToString(array $params): array
    {
        if (isset($params['reply_markup'])) {
            $params['reply_markup'] = (string)$params['reply_markup'];
        }

        return $params;
    }

    /**
     * Sends a multipart/form-data request to Telegram Bot API and returns the result.
     * Used primarily for file uploads.
     *
     * @param string $endpoint
     * @param array $params
     * @param string $inputFileField
     *
     * @return TelegramResponse
     * @throws CouldNotUploadInputFile
     *
     */
    protected function uploadFile(string $endpoint, array $params, $inputFileField): TelegramResponse
    {
        //Check if the field in the $params array (that is being used to send the relative file), is a file id.
        if (!isset($params[$inputFileField])) {
            throw CouldNotUploadInputFile::missingParam($inputFileField);
        }

        if ($this->hasFileId($inputFileField, $params)) {
            return $this->post($endpoint, $params);
        }

        //Sending an actual file requires it to be sent using multipart/form-data
        return $this->post($endpoint, $this->prepareMultipartParams($params, $inputFileField), true);
    }

    /**
     * Prepare Multipart Params for File Upload.
     *
     * @param array $params
     * @param string $inputFileField
     *
     * @return array
     * @throws CouldNotUploadInputFile
     *
     */
    protected function prepareMultipartParams(array $params, $inputFileField): array
    {
        $this->validateInputFileField($params, $inputFileField);

        //Iterate through all param options and convert to multipart/form-data.
        return collect($params)
            ->reject(function ($value) {
                return null === $value;
            })
            ->map(function ($contents, $name) {
                return $this->generateMultipartData($contents, $name);
            })
            ->values()
            ->all();
    }

    /**
     * Generates the multipart data required when sending files to telegram.
     *
     * @param mixed $contents
     * @param string $name
     *
     * @return array
     */
    protected function generateMultipartData($contents, $name): array
    {
        if (!$this->isInputFile($contents)) {
            return compact('name', 'contents');
        }

        $filename = $contents->getFilename();
        $contents = $contents->getContents();

        return compact('name', 'contents', 'filename');
    }

    /**
     * Sends a request to Telegram Bot API and returns the result.
     *
     * @param string $method
     * @param string $endpoint
     * @param array $params
     *
     * @return TelegramResponse
     * @throws TelegramSDKException
     *
     */
    protected function sendRequest($method, $endpoint, array $params = []): TelegramResponse
    {
        $telegramRequest = $this->resolveTelegramRequest($method, $endpoint, $params);

        return $this->lastResponse = $this->getClient()->sendRequest($telegramRequest);
    }

    /**
     * Instantiates a new TelegramRequest entity.
     *
     * @param string $method
     * @param string $endpoint
     * @param array $params
     *
     * @return TelegramRequest
     */
    protected function resolveTelegramRequest($method, $endpoint, array $params = []): TelegramRequest
    {
        return (new TelegramRequest(
            $this->getAccessToken(),
            $method,
            $endpoint,
            $params,
            $this->isAsyncRequest()
        ))
            ->setTimeOut($this->getTimeOut())
            ->setConnectTimeOut($this->getConnectTimeOut());
    }

    /**
     * @param array $params
     * @param $inputFileField
     *
     * @throws CouldNotUploadInputFile
     */
    protected function validateInputFileField(array $params, $inputFileField): void
    {
        if (!isset($params[$inputFileField])) {
            throw CouldNotUploadInputFile::missingParam($inputFileField);
        }

        // All file-paths, urls, or file resources should be provided by using the InputFile object
        if ((!$params[$inputFileField] instanceof InputFile) && (is_string($params[$inputFileField]) && !$this->is_json($params[$inputFileField]))) {
            throw CouldNotUploadInputFile::inputFileParameterShouldBeInputFileEntity($inputFileField);
        }
    }

    /**
     * @param array $params
     * @param $fileUpload
     *
     * @return array
     */
    private function normalizeParams(array $params, $fileUpload)
    {
        if ($fileUpload) {
            return ['multipart' => $params];
        }

        return ['form_params' => $this->replyMarkupToString($params)];
    }
}
