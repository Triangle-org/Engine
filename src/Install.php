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

/**
 * Класс Install
 * Этот класс предназначен для установки и обновления плагина.
 */
class Install
{
    const TRIANGLE_PLUGIN = true;

    /**
     * @var array
     */
    protected static array $pathRelation = [
        'master',
        'support/bootstrap.php',
        'support/helpers.php',

        'support/helpers/dirs.php',
        'support/helpers/formats.php',
        'support/helpers/paths.php',
        'support/helpers/responses.php',
        'support/helpers/server.php',
        'support/helpers/web.php',

//        'support/Request.php',
//        'support/Response.php',
    ];

    /**
     * Установка плагина
     * @return void
     */
    public static function install(): void
    {
        static::installByRelation();
    }

    /**
     * Обновление плагина
     * @return void
     */
    public static function update(): void
    {
        static::installByRelation();
    }

    /**
     * Установка плагина по связи
     * @return void
     */
    public static function installByRelation(): void
    {
        foreach (static::$pathRelation as $source) {
            if ($pos = strrpos($source, '/')) {
                $parentDir = base_path(substr($source, 0, $pos));
                if (!is_dir($parentDir)) {
                    mkdir($parentDir, 0777, true);
                }
            }

            $sourceFile = __DIR__ . "/$source";
            $targetFile = base_path($source);

            self::delFile($targetFile);
            copy_dir($sourceFile, $targetFile, true);
            self::delFile($sourceFile);

            echo "Создан $source\r\n";
        }
    }

    /**
     * Удаление файла
     * @param string $filepath Путь к файлу
     * @return void
     */
    private static function delFile(string $filepath): void
    {
        if (is_file($filepath)) {
            @unlink($filepath);
        }
    }

    /**
     * Удаление плагина
     * @return void
     */
    public static function uninstall()
    {
    }
}
