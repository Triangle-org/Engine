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

/**
 * Класс Install
 * Этот класс предназначен для установки и обновления плагина.
 */
class Install
{
    public const TRIANGLE_PLUGIN = true;

    /**
     * @var array
     */
    protected static array $pathRelation = [
        'Install/master' => 'master',
        'Install/bootstrap.php' => 'support/bootstrap.php',
        'Install/helpers.php' => 'support/helpers.php',
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
        static::installByRelation(true);
    }

    /**
     * Удаление плагина
     * @return void
     */
    public static function uninstall(): void
    {
        self::uninstallByRelation();
    }

    /**
     * @param bool $overwrite
     * @return void
     */
    public static function installByRelation(bool $overwrite = false): void
    {
        foreach (static::$pathRelation as $source => $target) {
            $sourceFile = __DIR__ . "/$source";
            $targetFile = base_path($target);

            if ($pos = strrpos($target, '/')) {
                $parentDir = base_path(substr($target, 0, $pos));
                if (!is_dir($parentDir)) {
                    mkdir($parentDir, 0777, true);
                }
            }

            copy_dir($sourceFile, $targetFile, $overwrite);
            echo "Создан $targetFile\r\n";
        }
    }

    /**
     * @return void
     */
    public static function uninstallByRelation(): void
    {
        foreach (static::$pathRelation as $source => $target) {
            $targetFile = base_path($target);

            remove_dir($targetFile);
            echo "Удалён $target\r\n";
        }
    }
}
