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

use ErrorException;
use localzet\Server;
use Triangle\Database\Bootstrap as DatabaseBootstrap;
use Triangle\Events\Bootstrap as EventsBootstrap;
use Triangle\Middleware\Bootstrap as MiddlewareBootstrap;
use Triangle\Router;
use Triangle\Session\Bootstrap as SessionBootstrap;

class Autoload
{
    private const LOADERS = [
        [Bootstrap::class, 'start'],
        [MiddlewareBootstrap::class, 'start'],
        [DatabaseBootstrap::class, 'start'],
        [SessionBootstrap::class, 'start'],
        [EventsBootstrap::class, 'start'],
    ];

    public static function loadCore(): void
    {
        Environment::start();
        Config::reloadAll(['route', 'container']);
    }

    public static function loadAll(?Server $server = null): void
    {
        self::initializeEnvironment($server);

        static::files();

        foreach (self::LOADERS as $loader) {
            if (class_exists($loader[0]) && method_exists($loader[0], $loader[1])) {
                $loader[0]::{$loader[1]}($server);
            }
        }

        self::collectRouterConfigs($server);
    }

    public static function files(): void
    {
        $autoloadFiles = array_merge(
            config('autoload.files', []),
            glob(base_path('autoload/*.php')),
            glob(base_path('autoload/*/*/*.php'))
        );

        foreach ($autoloadFiles as $file) {
            include_once $file;
        }

        Plugin::app_reduce(function ($plugin, array $config): void {
            foreach ($config['autoload']['files'] ?? [] as $file) {
                include_once $file;
            }
        });

        Plugin::plugin_reduce(function ($vendor, $plugins, $plugin, array $config): void {
            foreach ($config['autoload']['files'] ?? [] as $file) {
                include_once $file;
            }
        });
    }

    private static function initializeEnvironment(?Server $server = null): void
    {
        Environment::start();
        Config::reloadAll(['route']);
        set_error_handler(
            fn($level, $message, $file = '', $line = 0): bool => (error_reporting() & $level) ? throw new ErrorException($message, 0, $level, $file, $line) : true
        );

        if ($server instanceof \localzet\Server) {
            register_shutdown_function(
                fn($start_time): int|bool => (time() - $start_time <= 1) ? sleep(1) : true,
                time()
            );
        }

        if (function_exists('config')) {
            date_default_timezone_set(
                config('server.default_timezone', config('app.default_timezone', 'Europe/Moscow'))
            );
        }
    }

    private static function collectRouterConfigs(?Server $server): void
    {
        if ($server instanceof \localzet\Server && class_exists(Router::class)) {
            $paths = [config_path()];
            foreach (scan_dir(plugin_path(), false) as $name) {
                $dir = plugin_path("$name/config");
                if (is_dir($dir)) {
                    $paths[] = $dir;
                }
            }

            Router::collect($paths);
        }
    }
}
