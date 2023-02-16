<?php

/**
 * @package     Triangle Engine
 * @link        https://github.com/Triangle-org/Engine
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

use Triangle\Engine\Bootstrap;
use support\Container;
use support\Event;
use support\Log;


class Events implements Bootstrap
{
    /**
     * @var array
     */
    protected static $events = [];

    /**
     * @param Server $server
     * @return void
     */
    public static function start($server)
    {
        if (empty(config('event')) && is_array(config('event')) && !empty(config('event.app.enable'))) {
            $events = [];
            foreach (config('event') as $event_name => $callbacks) {
                $callbacks = static::convertCallable($callbacks);
                if (is_callable($callbacks)) {
                    $events[$event_name] = [$callbacks];
                    Event::on($event_name, $callbacks);
                    continue;
                }
                if (!is_array($callbacks)) {
                    $msg = "Events: $event_name => " . var_export($callbacks, true) . " is not callable\n";
                    echo $msg;
                    Log::error($msg);
                    continue;
                }
                foreach ($callbacks as $callback) {
                    $callback = static::convertCallable($callback);
                    if (is_callable($callback)) {
                        $events[$event_name][] = $callback;
                        Event::on($event_name, $callback);
                        continue;
                    }
                    $msg = "Events: $event_name => " . var_export($callback, true) . " is not callable\n";
                    echo $msg;
                    Log::error($msg);
                }
            }
            static::$events = array_merge_recursive(static::$events, $events);
        }
    }

    protected static function convertCallable($callback)
    {
        if (\is_array($callback)) {
            $callback = \array_values($callback);
            if (isset($callback[1]) && \is_string($callback[0]) && \class_exists($callback[0])) {
                $callback = [Container::get($callback[0]), $callback[1]];
            }
        }
        return $callback;
    }
}
