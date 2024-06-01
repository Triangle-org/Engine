<?php

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

class Plugin
{
    protected static function getFunction(string $namespace, string $type): ?callable
    {
        $function = "\\{$namespace}Install::$type";
        return is_callable($function) ? $function : null;
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
            $installFunction = static::getFunction($namespace, 'install');
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
        $file = __DIR__ . '/../../../../support/helpers.php';
        if (is_file($file)) {
            require_once $file;
            return;
        }

        $file = __DIR__ . '/Install/helpers.php';
        if (is_file($file)) {
            require_once $file;
        }
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
            $updateFunction = static::getFunction($namespace, 'update');
            if ($updateFunction) {
                $updateFunction();
                continue;
            }
            $installFunction = static::getFunction($namespace, 'install');
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
            $uninstallFunction = static::getFunction($namespace, 'uninstall');
            if ($uninstallFunction) {
                $uninstallFunction();
            }
        }
    }
}
