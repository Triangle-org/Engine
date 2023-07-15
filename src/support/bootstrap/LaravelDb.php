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

namespace support\bootstrap;

use Illuminate\Container\Container as IlluminateContainer;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\MySqlConnection;
use Illuminate\Events\Dispatcher;
use Illuminate\Pagination\Paginator;
use support\database\MongoDB\Connection as MongodbConnection;
use localzet\Server;
use localzet\Timer;
use support\Container;
use Throwable;
use Triangle\Engine\Bootstrap;
use function class_exists;
use function config;

/**
 * Class Laravel
 */
class LaravelDb implements Bootstrap
{
    /**
     * @param Server|null $server
     *
     * @return void
     */
    public static function start(?Server $server): void
    {
        if (!class_exists(Capsule::class)) {
            return;
        }

        $config = config('database', []);
        $connections = $config['connections'] ?? [];
        if (!$connections) {
            return;
        }

        $capsule = new Capsule(IlluminateContainer::getInstance());

        $capsule->getDatabaseManager()->extend('mongodb', function ($config, $name) {
            $config['name'] = $name;
            return new MongodbConnection($config);
        });

        $default = $config['default'] ?? false;
        if ($default) {
            $defaultConfig = $connections[$config['default']];
            $capsule->addConnection($defaultConfig);
        }

        foreach ($connections as $name => $config) {
            $capsule->addConnection($config, $name);
        }

        if (class_exists(Dispatcher::class) && !$capsule->getEventDispatcher()) {
            $capsule->setEventDispatcher(Container::make(Dispatcher::class, [IlluminateContainer::getInstance()]));
        }

        $capsule->setAsGlobal();

        $capsule->bootEloquent();

        // Heartbeat
        if ($server) {
            Timer::add(55, function () use ($default, $connections, $capsule) {
                foreach ($capsule->getDatabaseManager()->getConnections() as $connection) {
                    /* @var MySqlConnection $connection * */
                    if ($connection->getConfig('driver') == 'mysql') {
                        try {
                            $connection->select('select 1');
                        } catch (Throwable) {
                        }
                    }
                }
            });
        }

        // Paginator
        if (class_exists(Paginator::class)) {
            if (method_exists(Paginator::class, 'queryStringResolver')) {
                Paginator::queryStringResolver(function () {
                    $request = request();
                    return $request?->queryString();
                });
            }
            Paginator::currentPathResolver(function () {
                $request = request();
                return $request ? $request->path() : '/';
            });
            Paginator::currentPageResolver(function ($pageName = 'page') {
                $request = request();
                if (!$request) {
                    return 1;
                }
                $page = (int)($request->input($pageName, 1));
                return $page > 0 ? $page : 1;
            });
        }
    }
}
