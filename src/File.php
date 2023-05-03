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

namespace Triangle\Engine;

use SplFileInfo;
use Triangle\Engine\Exception\FileException;
use function chmod;
use function is_dir;
use function mkdir;
use function pathinfo;
use function restore_error_handler;
use function set_error_handler;
use function sprintf;
use function strip_tags;
use function umask;

class File extends SplFileInfo
{

    /**
     * Переместить
     * @param string $destination
     * @return File
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
        return new self($destination);
    }
}
