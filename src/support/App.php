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
use Throwable;
use Triangle\Engine\Config;
use Triangle\Engine\Environment;
use Triangle\Engine\Util;
use function base_path;
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

        static::loadEnvironment();
        static::loadAllConfig(['route', 'container']);

        $errorReporting = config('app.error_reporting', E_ALL);
        if (isset($errorReporting)) {
            error_reporting($errorReporting);
        }
        if ($timezone = config('app.default_timezone')) {
            date_default_timezone_set($timezone);
        }

        $runtimeLogsPath = runtime_path('logs');
        if (!file_exists($runtimeLogsPath) || !is_dir($runtimeLogsPath)) {
            if (!mkdir($runtimeLogsPath, 0777, true)) {
                throw new RuntimeException("Failed to create runtime logs directory. Please check the permission.");
            }
        }

        $runtimeViewsPath = runtime_path('views');
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

        Server::$pidFile = config('server.pid_file');
        Server::$stdoutFile = config('server.stdout_file', '/dev/null');
        Server::$logFile = config('server.log_file');
        TcpConnection::$defaultMaxPackageSize = config('server.max_package_size', 10 * 1024 * 1024);
        if (property_exists(Server::class, 'statusFile')) {
            Server::$statusFile = config('server.status_file', '');
        }
        if (property_exists(Server::class, 'stopTimeout')) {
            Server::$stopTimeout = config('server.stop_timeout', 2);
        }

        if (config('server.listen')) {
            $config = config('server');
            server_start(
                $config['name'],
                $config + [
                    'handler' => \Triangle\Engine\App::class,
                    'constructor' => [
                        'requestClass' => config('app.request_class', Request::class),
                        'logger' => Log::channel(),
                        'basePath' => BASE_PATH,
                        'appPath' => app_path(),
                        'publicPath' => public_path(),
                    ]
                ]
            );
        }

        // Windows не поддерживает кастомные процессы
        if (DIRECTORY_SEPARATOR === '/') {
            foreach (config('process', []) as $processName => $config) {
                // Отключим монитор в phar
                if (is_phar() && 'monitor' === $processName) {
                    continue;
                }
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
        $directory = base_path('plugin');
        foreach (scan_dir($directory, false) as $name) {
            $dir = "$directory/$name/config";
            if (is_dir($dir)) {
                Config::load($dir, $excludes, "plugin.$name");
            }
        }
    }

    private static function loadEnvironment(): void
    {
        Environment::load(config('env_file', '.env'));
    }
}
