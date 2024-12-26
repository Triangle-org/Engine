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

use function base_path;
use function copy_dir;
use function create_dir;
use function is_dir;
use function remove_dir;

/**
 * Класс Install
 * Этот класс предназначен для установки и обновления плагина.
 */
class Install
{
    public const TRIANGLE_PLUGIN = true;

    protected static array $pathRelation = [
        'Install/master' => 'master'
    ];

    public static function install(): void
    {
        foreach (static::$pathRelation as $source => $target) {
            if ($target === 'master' && !class_exists('Triangle\Console')) {
                continue;
            }

            self::copyDirectory($source, $target);
        }
    }

    public static function uninstall(): void
    {
        foreach (static::$pathRelation as $target) {
            self::removeDirectory($target);
        }
    }

    private static function copyDirectory(string $source, string $target): void
    {
        $sourceFile = __DIR__ . "/$source";
        $targetFile = base_path($target);

        self::createParentDirectory($target);

        copy_dir($sourceFile, $targetFile, true);
        echo "Создан $targetFile\r\n";
    }

    private static function removeDirectory(string $target): void
    {
        $targetFile = base_path($target);
        remove_dir($targetFile);
        echo "Удалён $target\r\n";
    }

    private static function createParentDirectory(string $path): void
    {
        if ($pos = strrpos($path, '/')) {
            $parentDir = base_path(substr($path, 0, $pos));
            if (!is_dir($parentDir)) {
                create_dir($parentDir);
            }
        }
    }
}
