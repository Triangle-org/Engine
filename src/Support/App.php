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

        Environment::start();
        Config::reloadAll(['route', 'container']);

        error_reporting(config('server.error_reporting', E_ALL));
        date_default_timezone_set(config('server.default_timezone', 'Europe/Moscow'));

        $runtimeLogsPath = runtime_path('logs');
        if (!file_exists($runtimeLogsPath) || !is_dir($runtimeLogsPath)) {
            if (!mkdir($runtimeLogsPath, 0777, true)) {
                throw new RuntimeException("Failed to create runtime logs directory. Please check the permission.");
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

        Server::$pidFile = config('server.pid_file', runtime_path('triangle.pid'));
        Server::$stdoutFile = config('server.stdout_file', runtime_path('logs/stdout.log'));
        Server::$logFile = config('server.log_file', runtime_path('logs/server.log'));
        Server::$statusFile = config('server.status_file', runtime_path('triangle.status'));
        Server::$stopTimeout = (int)config('server.stop_timeout', 2);
        TcpConnection::$defaultMaxPackageSize = config('server.max_package_size', 10 * 1024 * 1024);

        server_start(config('server.name'), config('server'));

        // Windows не поддерживает кастомные процессы
        if (DIRECTORY_SEPARATOR === '/') {
            foreach (config('servers', config('process', [])) as $processName => $config) {
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
                    foreach ($projects['servers'] ?? $project['process'] ?? [] as $processName => $config) {
                        server_start("plugin.$firm.$name.$processName", $config);
                    }
                }
                foreach ($projects['servers'] ?? $projects['process'] ?? [] as $processName => $config) {
                    server_start("plugin.$firm.$processName", $config);
                }
            }
        }

        if (!defined('GLOBAL_START')) {
            Server::runAll();
        }
    }
}
