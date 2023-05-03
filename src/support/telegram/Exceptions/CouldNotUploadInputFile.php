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

use support\telegram\FileUpload\InputFile;

/**
 * Class CouldNotUploadInputFile.
 */
final class CouldNotUploadInputFile extends TelegramSDKException
{
    public static function fileDoesNotExistOrNotReadable($file): self
    {
        return new self(sprintf('File: `%s` does not exist or is not readable!', $file));
    }

    public static function filenameNotProvided($path): self
    {
        $file = is_string($path) ? $path : "the resource that you're trying to upload";

        return new self(
            sprintf('Filename not provided for %s. ', $file) .
            'Remote or Resource file uploads require a filename. Refer Docs for more information.'
        );
    }

    public static function couldNotOpenResource($path): self
    {
        return new self(sprintf('Failed to create InputFile entity. Unable to open resource: %s.', $path));
    }

    public static function inputFileParameterShouldBeInputFileEntity($property): self
    {
        return new self(sprintf('A path to local file, a URL, or a file resource should be uploaded using `' . InputFile::class . '::create($pathOrUrlOrResource, $filename)` for `%s` property. Please view docs for example.', $property));
    }

    public static function missingParam($inputFileField): self
    {
        return new self(sprintf('Input field [%s] is missing in your params. Please make sure it exists and is an `support\telegram\FileUpload\InputFile` entity.', $inputFileField));
    }
}
