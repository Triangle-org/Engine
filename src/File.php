<?php declare(strict_types=1);

/**
 * @package     Triangle Engine (FrameX Project)
 * @link        https://github.com/Triangle-org/Engine Triangle Engine (v2+)
 * @link        https://github.com/localzet-archive/FrameX-Public FrameX (v1-2)
 *
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright (c) 2023-2024 Triangle Framework Team
 * @license     https://www.gnu.org/licenses/agpl-3.0 GNU Affero General Public License v3.0
 *
 *              This program is free software: you can redistribute it and/or modify
 *              it under the terms of the GNU Affero General Public License as published
 *              by the Free Software Foundation, either version 3 of the License, or
 *              (at your option) any later version.
 *
 *              This program is distributed in the hope that it will be useful,
 *              but WITHOUT ANY WARRANTY; without even the implied warranty of
 *              MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *              GNU Affero General Public License for more details.
 *
 *              You should have received a copy of the GNU Affero General Public License
 *              along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 *              For any questions, please contact <support@localzet.com>
 */

namespace Triangle\Engine;

use SplFileInfo;
use Triangle\Exception\FileException;
use function chmod;
use function is_dir;
use function mkdir;
use function pathinfo;
use function restore_error_handler;
use function set_error_handler;
use function sprintf;
use function strip_tags;
use function umask;

/**
 * Класс File
 * Этот класс представляет собой пользовательский файл, который был загружен.
 * Он наследует от базового класса SplFileInfo и добавляет дополнительные свойства и методы, специфичные для загруженных файлов.
 *
 * @link https://www.php.net/manual/en/class.splfileinfo.php
 */
class File extends SplFileInfo
{
    /**
     * @var string|null $uploadName Имя файла, указанное клиентом при загрузке.
     */
    protected ?string $uploadName = null;

    /**
     * @var string|null $uploadMimeType MIME-тип файла, указанный клиентом при загрузке.
     */
    protected ?string $uploadMimeType = null;

    /**
     * @var int|null $uploadErrorCode Код ошибки, возникшей при загрузке файла.
     */
    protected ?int $uploadErrorCode = null;

    /**
     * Конструктор класса File.
     *
     * @param string $fileName Имя файла на сервере.
     * @param string $uploadName Имя файла, указанное клиентом.
     * @param string $uploadMimeType MIME-тип файла.
     * @param int $uploadErrorCode Код ошибки загрузки.
     */
    public function __construct(string $fileName, string $uploadName, string $uploadMimeType, int $uploadErrorCode)
    {
        $this->uploadName = $uploadName;
        $this->uploadMimeType = $uploadMimeType;
        $this->uploadErrorCode = $uploadErrorCode;
        parent::__construct($fileName);
    }

    /**
     * Получить имя файла, указанное клиентом.
     *
     * @return string|null
     */
    public function getUploadName(): ?string
    {
        return $this->uploadName;
    }

    /**
     * Получить MIME-тип файла.
     *
     * @return string|null
     */
    public function getUploadMimeType(): ?string
    {
        return $this->uploadMimeType;
    }

    /**
     * Получить расширение файла.
     *
     * @return string
     */
    public function getUploadExtension(): string
    {
        return pathinfo($this->uploadName, PATHINFO_EXTENSION);
    }

    /**
     * Получить код ошибки загрузки.
     *
     * @return int|null
     */
    public function getUploadErrorCode(): ?int
    {
        return $this->uploadErrorCode;
    }

    /**
     * Проверить, является ли загрузка файла действительной.
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->uploadErrorCode === UPLOAD_ERR_OK;
    }

    /**
     * Перемещение файла.
     *
     * @param string $destination Путь назначения.
     * @return File Возвращает новый объект File для перемещенного файла.
     * @throws FileException Если возникает ошибка при перемещении файла.
     */
    public function move(string $destination): File
    {
        set_error_handler(function ($type, $msg) use (&$error) {
            $error = $msg;
        });
        $path = pathinfo($destination, PATHINFO_DIRNAME);
        if (!is_dir($path) && !mkdir($path, 0777, true)) {
            restore_error_handler();
            throw new FileException(sprintf('Unable to create the "%s" directory (%s)', $path, strip_tags($error)));
        }
        if (!rename($this->getPathname(), $destination)) {
            restore_error_handler();
            throw new FileException(sprintf('Could not move the file "%s" to "%s" (%s)', $this->getPathname(), $destination, strip_tags($error)));
        }
        restore_error_handler();
        @chmod($destination, 0666 & ~umask());

        return new self($destination, $this->uploadName, $this->uploadMimeType, $this->uploadErrorCode);
    }
}

