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

namespace Triangle\Engine\Autoload;

use support\Container;
use support\Events;
use support\Log;

class EventLoader
{
    public static function loadAll(): void
    {
        $rawEvents = config('event', []);

        $plugins = config('plugin', []);
        foreach ($plugins as $firm => $projects) {
            foreach ($projects as $name => $project) {
                if (is_array($project) && !empty($project['event'])) {
                    $rawEvents += $project['event'];
                }
            }
            if (!empty($projects['event'])) {
                $rawEvents += $projects['event'];
            }
        }

        self::load($rawEvents);
    }

    public static function load(array $events): void
    {
        $allEvents = [];
        foreach ($events as $eventName => $callbacks) {
            $callbacks = self::convertCallable($callbacks);
            if (!is_callable($callbacks) && !is_array($callbacks)) {
                self::log("Событие: $eventName => " . var_export($callbacks, true) . " не вызываемо\n");
                continue;
            }

            if (is_callable($callbacks)) {
                $allEvents[$eventName][] = [$callbacks];
                continue;
            }

            ksort($callbacks, SORT_NATURAL);
            foreach ($callbacks as $id => $callback) {
                $callback = self::convertCallable($callback);
                if (!is_callable($callback)) {
                    self::log("Событие: $eventName => " . var_export($callback, true) . " не вызываемо\n");
                    continue;
                }
                $allEvents[$eventName][$id][] = $callback;
            }
        }

        foreach ($allEvents as $name => $events) {
            ksort($events, SORT_NATURAL);
            foreach ($events as $callbacks) {
                foreach ($callbacks as $callback) {
                    Events::on($name, $callback);
                }
            }
        }
    }

    protected static function log($text): void
    {
        echo $text;
        Log::error($text);
    }

    /**
     * Преобразует колбэк(и) в массив колбэков
     * @param mixed $callbacks
     * @return array|mixed
     */
    protected static function convertCallable(mixed $callbacks): mixed
    {
        if (is_array($callbacks)) {
            $callback = array_values($callbacks);
            if (isset($callback[1]) && is_string($callback[0]) && class_exists($callback[0])) {
                return [Container::get($callback[0]), $callback[1]];
            }
        }
        return $callbacks;
    }
}