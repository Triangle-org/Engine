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

class Plugin
{
    protected static function getInstallFunction(string $namespace): ?callable
    {
        $installFunction = "\\{$namespace}Install::install";
        return is_callable($installFunction) ? $installFunction : null;
    }

    protected static function getUpdateFunction(string $namespace): ?callable
    {
        $updateFunction = "\\{$namespace}Install::update";
        return is_callable($updateFunction) ? $updateFunction : null;
    }

    protected static function getUninstallFunction(string $namespace): ?callable
    {
        $uninstallFunction = "\\{$namespace}Install::uninstall";
        return is_callable($uninstallFunction) ? $uninstallFunction : null;
    }

    /**
     * Install.
     * @param mixed $event
     * @return void
     */
    public static function install(mixed $event): void
    {
        static::findHelper();
        $psr4 = static::getPsr4($event);
        foreach ($psr4 as $namespace => $path) {
            $pluginConst = "\\{$namespace}Install::TRIANGLE_PLUGIN";
            if (!defined($pluginConst)) {
                continue;
            }
            $installFunction = static::getInstallFunction($namespace);
            if ($installFunction) {
                $installFunction(true);
            }
        }
    }

    /**
     * FindHelper.
     * @return void
     */
    protected static function findHelper(): void
    {
        // Plugin.php in vendor
        $file = __DIR__ . '/../../../../../support/helpers.php';
        if (is_file($file)) {
            require_once $file;
            return;
        }
        // Plugin.php in webman
        require_once __DIR__ . '/helpers.php';
    }

    /**
     * Get psr-4 info
     *
     * @param mixed $event
     * @return array
     */
    protected static function getPsr4(mixed $event): array
    {
        $operation = $event->getOperation();
        $autoload = method_exists($operation, 'getPackage') ? $operation->getPackage()->getAutoload() : $operation->getTargetPackage()->getAutoload();
        return $autoload['psr-4'] ?? [];
    }

    /**
     * Update.
     * @param mixed $event
     * @return void
     */
    public static function update(mixed $event): void
    {
        static::findHelper();
        $psr4 = static::getPsr4($event);
        foreach ($psr4 as $namespace => $path) {
            $pluginConst = "\\{$namespace}Install::TRIANGLE_PLUGIN";
            if (!defined($pluginConst)) {
                continue;
            }
            $updateFunction = static::getUpdateFunction($namespace);
            if ($updateFunction) {
                $updateFunction();
                continue;
            }
            $installFunction = static::getInstallFunction($namespace);
            if ($installFunction) {
                $installFunction(false);
            }
        }
    }

    /**
     * Uninstall.
     * @param mixed $event
     * @return void
     */
    public static function uninstall(mixed $event): void
    {
        static::findHelper();
        $psr4 = static::getPsr4($event);
        foreach ($psr4 as $namespace => $path) {
            $pluginConst = "\\{$namespace}Install::TRIANGLE_PLUGIN";
            if (!defined($pluginConst)) {
                continue;
            }
            $uninstallFunction = static::getUninstallFunction($namespace);
            if ($uninstallFunction) {
                $uninstallFunction();
            }
        }
    }
}
