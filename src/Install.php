<?php

/**
 * @package     Triangle Engine
 * @link        https://github.com/Triangle-org/Engine Triangle Engine (v2+)
 * @link        https://github.com/localzet-archive/FrameX-Public FrameX (v1-2)
 *
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright (c) 2018-2024 Zorin Projects S.P.
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
 *              For any questions, please contact <creator@localzet.com>
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
    ];

    /**
     * Установка плагина
     * @return void
     */
    public static function install(): void
    {
        static::installByRelation(true);
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
     * @param bool $install
     * @return void
     */
    public static function installByRelation(bool $install = false): void
    {
        foreach (static::$pathRelation as $source) {
            $sourceFile = __DIR__ . "/$source";
            $targetFile = base_path($source);

            if ($pos = strrpos($source, '/')) {
                $parentDir = base_path(substr($source, 0, $pos));
                if (!is_dir($parentDir)) {
                    mkdir($parentDir, 0777, true);
                }
            }

            $install && self::delFile($targetFile);
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
