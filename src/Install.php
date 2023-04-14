<?php

/**
 * @package     Triangle Engine
 * @link        https://github.com/Triangle-org/Engine
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
    protected static $pathRelation = [
        'master' => 'master',
        // 'start.php' => 'start.php',
        // 'windows.php' => 'windows.php',
        'support/bootstrap.php' => 'support/bootstrap.php',
        // 'support/helpers.php' => 'support/helpers.php',
    ];

    /**
     * Install
     * @return void
     */
    public static function install()
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
    public static function installByRelation()
    {
        foreach (static::$pathRelation as $source => $dest) {
            if ($pos = \strrpos($dest, '/')) {
                $parentDir = base_path() . '/' . \substr($dest, 0, $pos);
                if (!\is_dir($parentDir)) {
                    \mkdir($parentDir, 0777, true);
                }
            }
            $sourceFile = __DIR__ . "/$source";
            copy_dir($sourceFile, base_path() . "/$dest", true);
            echo "Создан $dest\r\n";
            if (\is_file($sourceFile)) {
                @\unlink($sourceFile);
            }
        }
    }
}
