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

use localzet\Server;
use support\Container;
use support\Event;
use support\Log;
use Triangle\Engine\Bootstrap;
use function array_values;
use function class_exists;
use function is_array;
use function is_string;

/**
 * @deprecated
 */
class Events implements Bootstrap
{
    /**
     * @var array
     */
    protected static array $events = [];

    /**
     * @param Server $server
     * @return void
     */
    public static function start($server): void
    {
        static::getEvents();
        foreach (static::$events as $name => $events) {
            // Сортировка 1 2 3 ... 9 a b c...z
            ksort($events, SORT_NATURAL);
            foreach ($events as $callbacks) {
                foreach ($callbacks as $callback) {
                    Event::on($name, $callback);
                }
            }
        }
    }

    protected static function convertCallable($callbacks): array
    {
        if (is_array($callbacks)) {
            $callback = array_values($callbacks);
            if (isset($callback[1]) && is_string($callback[0]) && class_exists($callback[0])) {
                return [Container::get($callback[0]), $callback[1]];
            }
        }
        return $callback ?? [];
    }

    /**
     * @return void
     */
    protected static function getEvents(): void
    {
        if (!empty(config('event')) && is_array(config('event'))) {
            foreach (config('event') as $event_name => $callbacks) {
                $callbacks = static::convertCallable($callbacks);
                if (is_callable($callbacks)) {
                    static::$events[$event_name][] = [$callbacks];
                    continue;
                }
                ksort($callbacks, SORT_NATURAL);
                foreach ($callbacks as $id => $callback) {
                    $callback = static::convertCallable($callback);
                    if (is_callable($callback)) {
                        static::$events[$event_name][$id][] = $callback;
                        continue;
                    }
                    $msg = "Событие: $event_name => " . var_export($callback, true) . " не вызываем\n";
                    echo $msg;
                    Log::error($msg);
                }
            }
        }
    }
}
