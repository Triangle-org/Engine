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

class Install
{
    const FRAMEX_PLUGIN = true;

    /**
     * @var array
     */
    protected static array $pathRelation = [
        'master' => 'master',
        'support/bootstrap.php' => 'support/bootstrap.php',
        // 'support/helpers.php' => 'support/helpers.php',
    ];

    /**
     * Install
     * @return void
     */
    public static function install(): void
    {
        static::installByRelation();
    }

    /**
     * Uninstall
     * @return void
     */
    public static function uninstall()
    {
    }

    /**
     * InstallByRelation
     * @return void
     */
    public static function installByRelation(): void
    {
        foreach (static::$pathRelation as $source => $dest) {
            if ($pos = strrpos($dest, '/')) {
                $parentDir = base_path() . '/' . substr($dest, 0, $pos);
                if (!is_dir($parentDir)) {
                    mkdir($parentDir, 0777, true);
                }
            }
            $sourceFile = __DIR__ . "/$source";
            copy_dir($sourceFile, base_path() . "/$dest", true);
            echo "Создан $dest\r\n";
            if (is_file($sourceFile)) {
                @unlink($sourceFile);
            }
        }
    }
}
