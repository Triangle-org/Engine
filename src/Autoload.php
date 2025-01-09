<?php declare(strict_types=1);
/**
 * @package     Triangle Engine (FrameX Project)
 * @link        https://github.com/Triangle-org/Engine Triangle Engine (v2+)
 * @link        https://github.com/localzet-archive/FrameX-Public FrameX (v1-2)
 *
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright (c) 2023-2025 Triangle Framework Team
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

class Autoload
{
    public static function start(?string $arg = null, ?Server $server = null): void
    {
        if (in_array($arg ?? '', ['start', 'restart', 'stop', 'status', 'reload', 'connections'])) {
            // Предзагрузка
            Config::reloadAll(['route', 'container']);
            static::system();
        } else if ($server instanceof Server) {
            // При старте сервера
            Config::reloadAll(['route']);
            register_shutdown_function(fn($s): int|bool => (time() - $s <= 1) ? sleep(1) : true, time());
            static::system();
            static::files();
            Bootstrap::start($server);
            Context::init();
        } else {
            // CLI
            Config::reloadAll(['route']);
            set_error_handler(fn($l, $m, $f = '', $n = 0): bool => (error_reporting() & $l) ? throw new ErrorException($m, 0, $l, $f, $n) : true);
            static::system();
            static::files();
            Bootstrap::start();
        }
    }

    private static function system(): void
    {
        ini_set('display_errors', 'on');
        error_reporting(config('server.error_reporting', E_ALL));
        date_default_timezone_set(config('server.default_timezone', config('app.default_timezone', 'Europe/Moscow')));
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
}
