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

namespace support;

use Dotenv\Dotenv;
use localzet\Server\Connection\TcpConnection;
use localzet\Server;
use RuntimeException;
use Throwable;
use Triangle\Engine\Config;
use Triangle\Engine\Util;
use function base_path;
use function call_user_func;
use function is_dir;
use function opcache_get_status;
use function opcache_invalidate;
use const DIRECTORY_SEPARATOR;

class App
{
    /**
     * @return void
     * @throws Throwable
     */
    public static function run(): void
    {
        ini_set('display_errors', 'on');

        if (class_exists(Dotenv::class) && file_exists(run_path('.env'))) {
            if (method_exists(Dotenv::class, 'createUnsafeImmutable')) {
                Dotenv::createUnsafeImmutable(run_path())->load();
            } else {
                Dotenv::createMutable(run_path())->load();
            }
        }

        static::loadAllConfig(['route', 'container']);

        $errorReporting = config('app.error_reporting', E_ALL);
        if (isset($errorReporting)) {
            error_reporting($errorReporting);
        }
        if ($timezone = config('app.default_timezone')) {
            date_default_timezone_set($timezone);
        }

        $runtimeLogsPath = runtime_path() . DIRECTORY_SEPARATOR . 'logs';
        if (!file_exists($runtimeLogsPath) || !is_dir($runtimeLogsPath)) {
            if (!mkdir($runtimeLogsPath, 0777, true)) {
                throw new RuntimeException("Failed to create runtime logs directory. Please check the permission.");
            }
        }

        $runtimeViewsPath = runtime_path() . DIRECTORY_SEPARATOR . 'views';
        if (!file_exists($runtimeViewsPath) || !is_dir($runtimeViewsPath)) {
            if (!mkdir($runtimeViewsPath, 0777, true)) {
                throw new RuntimeException("Failed to create runtime views directory. Please check the permission.");
            }
        }

        Server::$onMasterReload = function () {
            if (function_exists('opcache_get_status')) {
                if ($status = opcache_get_status()) {
                    if (isset($status['scripts']) && $scripts = $status['scripts']) {
                        foreach (array_keys($scripts) as $file) {
                            opcache_invalidate($file, true);
                        }
                    }
                }
            }
        };

        $config = config('server');
        Server::$pidFile = $config['pid_file'];
        Server::$stdoutFile = $config['stdout_file'];
        Server::$logFile = $config['log_file'];
        Server::$eventLoopClass = $config['event_loop'] ?? '';
        TcpConnection::$defaultMaxPackageSize = $config['max_package_size'] ?? 10 * 1024 * 1024;
        if (property_exists(Server::class, 'statusFile')) {
            Server::$statusFile = $config['status_file'] ?? '';
        }
        if (property_exists(Server::class, 'stopTimeout')) {
            Server::$stopTimeout = $config['stop_timeout'] ?? 2;
        }

        if ($config['listen']) {
            $server = new Server($config['listen'], $config['context']);
            $propertyMap = [
                'name',
                'count',
                'user',
                'group',
                'reusePort',
                'transport',
                'protocol'
            ];
            foreach ($propertyMap as $property) {
                if (isset($config[$property])) {
                    $server->$property = $config[$property];
                }
            }

            $server->onServerStart = function ($server) {
                require_once base_path() . '/support/bootstrap.php';
                $app = new \Triangle\Engine\App(config('app.request_class', Request::class), Log::channel(), app_path(), public_path());
                $server->onMessage = [$app, 'onMessage'];
                call_user_func([$app, 'onServerStart'], $server);
            };
        }

        // Windows does not support custom processes.
        if (DIRECTORY_SEPARATOR === '/') {
            foreach (config('process', []) as $processName => $config) {
                server_start($processName, $config);
            }
            foreach (config('plugin', []) as $firm => $projects) {
                foreach ($projects as $name => $project) {
                    if (!is_array($project)) {
                        continue;
                    }
                    foreach ($project['process'] ?? [] as $processName => $config) {
                        server_start("plugin.$firm.$name.$processName", $config);
                    }
                }
                foreach ($projects['process'] ?? [] as $processName => $config) {
                    server_start("plugin.$firm.$processName", $config);
                }
            }
        }

        if (!defined('GLOBAL_START')) {
            Server::runAll();
        }
    }

    /**
     * @param array $excludes
     * @return void
     */
    public static function loadAllConfig(array $excludes = []): void
    {
        Config::load(config_path(), $excludes);
        $directory = base_path() . '/plugin';
        foreach (Util::scanDir($directory, false) as $name) {
            $dir = "$directory/$name/config";
            if (is_dir($dir)) {
                Config::load($dir, $excludes, "plugin.$name");
            }
        }
    }
}
