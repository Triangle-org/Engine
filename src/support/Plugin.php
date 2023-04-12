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

namespace support;

class Plugin
{
    /**
     * @param mixed $event
     * @return void
     */
    public static function install($event)
    {
        $psr4 = static::getPsr4($event);
        foreach ($psr4 as $namespace => $path) {
            $pluginConst  = "\\{$namespace}Install::FRAMEX_PLUGIN";
            if (!defined($pluginConst)) {
                continue;
            }
            $installFunction = "\\{$namespace}Install::install";
            if (is_callable($installFunction)) {
                $installFunction(true);
            }
        }
    }

    /**
     * @param mixed $event
     * @return void
     */
    public static function update($event)
    {
        $psr4 = static::getPsr4($event);
        foreach ($psr4 as $namespace => $path) {
            $pluginConst = "\\{$namespace}Install::FRAMEX_PLUGIN";
            if (!defined($pluginConst)) {
                continue;
            }
            $updateFunction = "\\{$namespace}Install::update";
            if (is_callable($updateFunction)) {
                $updateFunction();
                continue;
            }
            $installFunction = "\\{$namespace}Install::install";
            if (is_callable($installFunction)) {
                $installFunction(false);
            }
        }
    }

    /**
     * @param mixed $event
     * @return void
     */
    public static function uninstall($event)
    {
        $psr4 = static::getPsr4($event);
        foreach ($psr4 as $namespace => $path) {
            $pluginConst = "\\{$namespace}Install::FRAMEX_PLUGIN";
            if (!defined($pluginConst)) {
                continue;
            }
            $uninstallFunction = "\\{$namespace}Install::uninstall";
            if (is_callable($uninstallFunction)) {
                $uninstallFunction();
            }
        }
    }

    /**
     * Get psr-4 info
     *
     * @param mixed $event
     * @return array
     */
    protected static function getPsr4($event)
    {
        $operation = $event->getOperation();
        $autoload = method_exists($operation, 'getPackage') ? $operation->getPackage()->getAutoload() : $operation->getTargetPackage()->getAutoload();
        return $autoload['psr-4'] ?? [];
    }

    /**
     * @return void
     */
    protected static function findHepler()
    {
        // Plugin.php in vendor
        $file = __DIR__ . '/../../../../../support/helpers.php';
        if (\is_file($file)) {
            require_once $file;
            return;
        }
        // Plugin.php
        require_once __DIR__ . '/helpers.php';
    }
}
