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

use support\telegram\FileUpload\InputFile;

/**
 * Validator.
 */
trait Validator
{
    /**
     * Determine given param in params array is a file id.
     */
    protected function hasFileId(string $inputFileField, array $params): bool
    {
        return isset($params[$inputFileField]) && $this->isFileId($params[$inputFileField]);
    }

    /**
     * Determine if given contents are an instance of InputFile.
     */
    protected function isInputFile($contents): bool
    {
        return $contents instanceof InputFile;
    }

    /**
     * Determine the given string is a file id.
     *
     * @param string|InputFile|resource $value
     */
    protected function isFileId(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        return preg_match('#^[\w\-]{20,}+$#u', trim($value)) > 0;
    }

    /**
     * Determine given string is a URL.
     *
     * @param string $value A filename or URL to a sticker
     */
    protected function isUrl(string $value): bool
    {
        return (bool)filter_var($value, FILTER_VALIDATE_URL);
    }

    /**
     * Determine given string is a json object.
     *
     * @param string $string A json string
     */
    protected function is_json(string $string): bool
    {
        json_decode($string, false);

        return json_last_error() === JSON_ERROR_NONE;
    }
}
