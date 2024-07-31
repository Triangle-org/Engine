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

use localzet\Server;
use Triangle\Router;

class Autoload
{
    private const LOADERS = [
        [Bootstrap::class, 'start'],

        [\Triangle\Middleware\Bootstrap::class, 'start'],
        [\Triangle\Database\Bootstrap::class, 'start'],
        [\Triangle\Session\Bootstrap::class, 'start'],
        [\Triangle\Events\Bootstrap::class, 'start'],
    ];

    public static function loadCore(): void
    {
        Environment::start();
        Config::reloadAll(['route', 'container']);
    }

    public static function loadAll(?Server $server = null): void
    {
        Environment::start();
        Config::reloadAll(['route']);

        set_error_handler(fn($level, $message, $file = '', $line = 0) => (error_reporting() & $level) ? throw new ErrorException($message, 0, $level, $file, $line) : true);
        if ($server) register_shutdown_function(fn($start_time) => (time() - $start_time <= 1) ? sleep(1) : true, time());
        if (function_exists('config')) date_default_timezone_set(config('app.default_timezone', 'Europe/Moscow'));

        static::files();

        foreach (self::LOADERS as $loader) {
            if (class_exists($loader[0]) && method_exists($loader[0], $loader[1])) {
                $loader[0]::{$loader[1]}($server);
            }
        }

        if (class_exists(Router::class)) {
            $paths = [config_path()];
            $directory = Path::basePath('plugin');
            foreach (scan_dir($directory, false) as $name) {
                $dir = "$directory/$name/config";
                if (is_dir($dir)) {
                    $paths[] = $dir;
                }
            }

            Router::collect($paths);
        }
    }

    public static function files(): void
    {
        foreach (config('autoload.files', []) as $file) {
            include_once $file;
        }

        foreach (glob(base_path('autoload/*.php')) as $file) {
            include_once($file);
        }

        foreach (glob(base_path('autoload/*/*/*.php')) as $file) {
            include_once($file);
        }

        foreach (config('plugin', []) as $firm => $projects) {
            foreach ($projects as $name => $project) {
                if (!is_array($project)) {
                    continue;
                }
                foreach ($project['autoload']['files'] ?? [] as $file) {
                    include_once $file;
                }
            }
            foreach ($projects['autoload']['files'] ?? [] as $file) {
                include_once $file;
            }
        }
    }
}