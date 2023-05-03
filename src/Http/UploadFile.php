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

namespace Triangle\Engine\Http;

use Triangle\Engine\File;
use function pathinfo;

/**
 * Class UploadFile
 */
class UploadFile extends File
{
    /**
     * @var string|null
     */
    protected ?string $uploadName = null;

    /**
     * @var string|null
     */
    protected ?string $uploadMimeType = null;

    /**
     * @var int|null
     */
    protected ?int $uploadErrorCode = null;

    /**
     * UploadFile constructor.
     *
     * @param string $fileName
     * @param string $uploadName
     * @param string $uploadMimeType
     * @param int $uploadErrorCode
     */
    public function __construct(string $fileName, string $uploadName, string $uploadMimeType, int $uploadErrorCode)
    {
        $this->uploadName = $uploadName;
        $this->uploadMimeType = $uploadMimeType;
        $this->uploadErrorCode = $uploadErrorCode;
        parent::__construct($fileName);
    }

    /**
     * @return string|null
     */
    public function getUploadName(): ?string
    {
        return $this->uploadName;
    }

    /**
     * @return string|null
     */
    public function getUploadMimeType(): ?string
    {
        return $this->uploadMimeType;
    }

    /**
     * @return string
     */
    public function getUploadExtension(): string
    {
        return pathinfo($this->uploadName, PATHINFO_EXTENSION);
    }

    /**
     * @return int|null
     */
    public function getUploadErrorCode(): ?int
    {
        return $this->uploadErrorCode;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->uploadErrorCode === UPLOAD_ERR_OK;
    }

}
