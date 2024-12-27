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

namespace support;

use localzet\Server;
use localzet\Server\Connection\TcpConnection;
use RuntimeException;
use Triangle\Engine\Config;
use Triangle\Engine\Environment;
use Triangle\Engine\Plugin;
use Triangle\Engine\Request;
use Triangle\Engine\Response;
use function is_dir;
use function opcache_get_status;
use function opcache_invalidate;

class App
{
    public static function run(): void
    {
        ini_set('display_errors', 'on');
        if (!class_exists(\Triangle\Request::class)) {
            class_alias(Request::class, \Triangle\Request::class);
        }

        if (!class_exists(\Triangle\Response::class)) {
            class_alias(Response::class, \Triangle\Response::class);
        }

        Environment::start();
        Config::reloadAll(['route', 'container']);

        error_reporting(config('server.error_reporting', E_ALL));
        date_default_timezone_set(config('server.default_timezone', config('app.default_timezone', 'Europe/Moscow')));

        $server = config('server.server');
        $server = $server instanceof Server ? $server : Server::class;

        $server::$onMasterReload = function (): void {
            if (function_exists('opcache_get_status') && ($status = opcache_get_status()) && isset($status['scripts'])) {
                foreach (array_keys($status['scripts']) as $file) {
                    opcache_invalidate($file, true);
                }
            }
        };

        foreach ([
                     'pidFile' => 'triangle.pid',
                     'statusFile' => 'triangle.status',
                     'stdoutFile' => 'logs/stdout.log',
                     'logFile' => 'logs/server.log',
                 ] as $key => $default
        ) {
            $path = runtime_path(config("server.master.$key", $default));
            if ((!file_exists(dirname($path)) || !is_dir(dirname($path))) && !create_dir(dirname($path))) {
                throw new RuntimeException("Failed to create runtime logs directory. Please check the permission.");
            }

            $server::$$key = $path;
        }

        if (is_array(config('server.tcp'))) {
            TcpConnection::$defaultMaxSendBufferSize = config('server.tcp.defaultMaxSendBufferSize', 1024 * 1024);
            TcpConnection::$defaultMaxPackageSize = config('server.tcp.defaultMaxPackageSize', 10 * 1024 * 1024);
        }

        $servers = [config('server')];

        // Windows не поддерживает кастомные процессы
        if (is_unix()) {
            $config = config();
            $services = fn($c) => $c['server']['services'] ?? $c['servers'] ?? $c['process'] ?? [];

            foreach ($services($config) as $processName => $processConfig) {
                $processConfig['name'] ??= $processName;
                $servers[] = $processConfig;
            }

            Plugin::app_reduce(function ($plugin, $config) use (&$servers, $services): void {
                foreach ($services($config) as $processName => $processConfig) {
                    $processConfig['name'] ??= config('app.plugin_alias', 'plugin') . ".$plugin.$processName";
                    $servers[] = $processConfig;
                }
            });

            Plugin::plugin_reduce(function ($vendor, $plugins, $plugin, $config) use (&$servers, $services): void {
                foreach ($services($config) as $processName => $processConfig) {
                    $processConfig['name'] ??= "plugin.$vendor.$plugin.$processName";
                    $servers[] = $processConfig;
                }
            });
        }

        foreach ($servers as $server) {
            localzet_start(
                name: $server['name'] ?? null,
                count: $server['count'] ?? null,
                listen: $server['listen'] ?? null,
                context: $server['context'] ?? null,
                user: $server['user'] ?? null,
                group: $server['group'] ?? null,
                reloadable: $server['reloadable'] ?? null,
                reusePort: $server['reusePort'] ?? null,
                protocol: $server['protocol'] ?? null,
                transport: $server['transport'] ?? null,
                server: $server['server'] ?? null,
                handler: $server['handler'] ?? null,
                constructor: $server['constructor'] ?? null,
                services: $server['services'] ?? null,
            );
        }

        if (!defined('GLOBAL_START')) {
            Server::runAll();
        }
    }
}
