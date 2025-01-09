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

use localzet\Server;
use support\Log;

class Bootstrap implements BootstrapInterface
{
    private const COMPONENTS = [
        \Triangle\Middleware\Bootstrap::class,
        \Triangle\Database\Bootstrap::class,
        \Triangle\Session\Bootstrap::class,
        \Triangle\Router\Bootstrap::class,
        \Triangle\Events\Bootstrap::class,
        \Triangle\Cron\Bootstrap::class,
    ];

    public static function start(?Server $server = null): void
    {
        self::load(static::COMPONENTS, $server, true);
        self::load(config('bootstrap', []), $server);

        Plugin::plugin_reduce(function ($vendor, $plugins, $plugin, $config) use ($server): void {
            self::load($config['bootstrap'] ?? [], $server);
        });

        Plugin::app_reduce(function ($plugin, $config) use ($server): void {
            self::load($config['bootstrap'] ?? [], $server);
        });
    }

    public static function load(array $classes, ?Server $server = null, bool $ignore = false): void
    {
        foreach ($classes as $class) {
            if (class_exists($class)
                && (($class instanceof BootstrapInterface) || method_exists($class, 'start'))
            ) {
                $class::start($server);
            } else if (!$ignore) {
                self::log("Внимание! Класса $class не существует\n");
            }
        }
    }

    protected static function log($text): void
    {
        echo $text;
        Log::error($text);
    }
}