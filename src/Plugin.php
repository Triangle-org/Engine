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

class Plugin
{
    public static function app_reduce($callback)
    {
        foreach (config(config('app.plugin_alias', 'plugin'), []) as $plugin => $config) {
            if (is_array($config)) {
                $callback($plugin, $config);
            }
        }
    }

    public static function app_by_path(string $path): ?string
    {
        $trimmedPath = trim($path, '/');
        $suffix = trim(config('app.plugin_uri', 'app'), '/');

        if (str_starts_with($trimmedPath, $suffix)) {
            $trimmedPath = trim(substr($trimmedPath, strlen($suffix)), '/');
            return explode('/', $trimmedPath)[0] ?? '';
        }

        return null;
    }

    public static function app_by_class(string $class): ?string
    {
        $trimmedClass = trim($class, '\\');
        $suffix = str_replace('\\', '/', trim(config('app.plugin_alias', 'plugin'), '/'));

        if (str_starts_with($trimmedClass, $suffix)) {
            $trimmedClass = trim(substr($trimmedClass, strlen($suffix)), '\\');
            return explode('\\', $trimmedClass)[0] ?? '';
        }

        return null;
    }

    public static function plugin_reduce($callback)
    {
        foreach (config('plugin', []) as $vendor => $plugins) {
            foreach ($plugins as $plugin => $config) {
                if (is_array($config)) {
                    $callback($vendor, $plugins, $plugin, $config);
                }
            }
        }
    }

    protected static function getFunction(string $namespace, string $type): ?callable
    {
        $function = "\\{$namespace}Install::$type";
        return is_callable($function) ? $function : null;
    }

    public static function install(mixed $event): void
    {
        static::findHelper();
        foreach (static::getPsr4($event) as $namespace => $path) {
            if (defined("\\{$namespace}Install::TRIANGLE_PLUGIN")) {
                $installFunction = static::getFunction($namespace, 'install');
                if ($installFunction) {
                    $installFunction(true);
                }
            }
        }
    }

    public static function update(mixed $event): void
    {
        static::findHelper();
        foreach (static::getPsr4($event) as $namespace => $path) {
            if (defined("\\{$namespace}Install::TRIANGLE_PLUGIN")) {
                $updateFunction = static::getFunction($namespace, 'update');
                if ($updateFunction) {
                    $updateFunction();
                } else {
                    $installFunction = static::getFunction($namespace, 'install');
                    if ($installFunction) {
                        $installFunction();
                    }
                }
            }
        }
    }

    public static function uninstall(mixed $event): void
    {
        static::findHelper();
        foreach (static::getPsr4($event) as $namespace => $path) {
            if (defined("\\{$namespace}Install::TRIANGLE_PLUGIN")) {
                $uninstallFunction = static::getFunction($namespace, 'uninstall');
                if ($uninstallFunction) {
                    $uninstallFunction();
                }
            }
        }
    }

    protected static function findHelper(): void
    {
        $file = __DIR__ . '/functions.php';
        if (is_file($file)) {
            require_once $file;
        }
    }

    protected static function getPsr4(mixed $event): array
    {
        $operation = $event->getOperation();
        $autoload = method_exists($operation, 'getPackage') ? $operation->getPackage()->getAutoload() : $operation->getTargetPackage()->getAutoload();
        return $autoload['psr-4'] ?? [];
    }
}
