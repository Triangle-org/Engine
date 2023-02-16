<?php

/**
 * @package     Triangle Engine
 * @link        https://github.com/Triangle-org/Engine
 * 
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright (c) 2018-2023 Localzet Group
 * @license     https://www.localzet.com/license GNU GPLv3 License
 */

namespace support\telegram\Traits;

use support\telegram\FileUpload\InputFile;

/**
 * Validator.
 */
trait Validator
{
    /**
     * Determine given param in params array is a file id.
     *
     * @param string $inputFileField
     * @param array  $params
     *
     * @return bool
     */
    protected function hasFileId(string $inputFileField, array $params): bool
    {
        return isset($params[$inputFileField]) && $this->isFileId($params[$inputFileField]);
    }

    /**
     * Determine if given contents are an instance of InputFile.
     *
     * @param $contents
     *
     * @return bool
     */
    protected function isInputFile($contents): bool
    {
        return $contents instanceof InputFile;
    }

    /**
     * Determine the given string is a file id.
     *
     * @param string $value
     *
     * @return bool
     */
    protected function isFileId($value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        return preg_match('/^[\w\-]{20,}+$/u', trim($value)) > 0;
    }

    /**
     * Determine given string is a URL.
     *
     * @param string $value A filename or URL to a sticker
     *
     * @return bool
     */
    protected function isUrl($value): bool
    {
        return (bool) filter_var($value, FILTER_VALIDATE_URL);
    }

    /**
     * Determine given string is a json object.
     *
     * @param string $string A json string
     * @return bool
     */
    protected function is_json($string): bool
    {
        json_decode($string);

        return json_last_error() == JSON_ERROR_NONE;
    }
}
