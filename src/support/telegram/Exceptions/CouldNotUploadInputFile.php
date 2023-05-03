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

namespace support\telegram\Exceptions;

/**
 * Class CouldNotUploadInputFile.
 */
class CouldNotUploadInputFile extends TelegramSDKException
{
    /**
     * @param $file
     *
     * @return CouldNotUploadInputFile
     */
    public static function fileDoesNotExistOrNotReadable($file): self
    {
        return new static("File: `{$file}` does not exist or is not readable!");
    }

    /**
     * @param $path
     *
     * @return CouldNotUploadInputFile
     */
    public static function filenameNotProvided($path): self
    {
        $file = is_string($path) ? $path : "the resource that you're trying to upload";

        return new static(
            "Filename not provided for {$file}. " .
            'Remote or Resource file uploads require a filename. Refer Docs for more information.'
        );
    }

    /**
     * @param $path
     *
     * @return CouldNotUploadInputFile
     */
    public static function couldNotOpenResource($path): self
    {
        return new static("Failed to create InputFile entity. Unable to open resource: {$path}.");
    }

    /**
     * @param $property
     *
     * @return CouldNotUploadInputFile
     */
    public static function inputFileParameterShouldBeInputFileEntity($property): self
    {
        return new static("A path to local file, a URL, or a file resource should be uploaded using `support\telegram\FileUpload\InputFile::create(\$pathOrUrlOrResource, \$filename)` for `{$property}` property. Please view docs for example.");
    }

    /**
     * @param $inputFileField
     *
     * @return CouldNotUploadInputFile
     */
    public static function missingParam($inputFileField): self
    {
        return new static("Input field [{$inputFileField}] is missing in your params. Please make sure it exists and is an `support\telegram\FileUpload\InputFile` entity.");
    }
}
