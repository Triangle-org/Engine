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

use Psr\Log\LoggerInterface;
use Throwable;

class Event
{
    /**
     * @var array
     */
    protected static array $eventMap = [];

    /**
     * @var array
     */
    protected static array $prefixEventMap = [];

    /**
     * @var int
     */
    protected static int $id = 0;

    /**
     * @var LoggerInterface
     */
    protected static LoggerInterface $logger;

    /**
     * @param mixed $event_name
     * @param callable $listener
     * @return int
     */
    public static function on(mixed $event_name, callable $listener): int
    {
        $is_prefix_name = $event_name[strlen($event_name) - 1] === '*';
        if ($is_prefix_name) {
            static::$prefixEventMap[substr($event_name, 0, -1)][++static::$id] = $listener;
        } else {
            static::$eventMap[$event_name][++static::$id] = $listener;
        }
        return static::$id;
    }

    /**
     * @param mixed $event_name
     * @param integer $id
     * @return int
     */
    public static function off(mixed $event_name, int $id): int
    {
        if (isset(static::$eventMap[$event_name][$id])) {
            unset(static::$eventMap[$event_name][$id]);
            return 1;
        }
        return 0;
    }

    /**
     * @param mixed $event_name
     * @param mixed $data
     * @param bool $halt
     * @return array|null|mixed
     */
    public static function emit(mixed $event_name, mixed $data, bool $halt = false): mixed
    {
        $listeners = static::getListeners($event_name);
        $responses = [];
        foreach ($listeners as $listener) {
            try {
                $response = $listener($data, $event_name);
            } catch (Throwable $e) {
                $responses[] = $e;
                if (!static::$logger && is_callable('\support\Log::error')) {
                    static::$logger = Log::channel();
                }
                static::$logger?->error($e);
                continue;
            }
            $responses[] = $response;
            if ($halt && !is_null($response)) {
                return $response;
            }
            if ($response === false) {
                break;
            }
        }
        return $halt ? null : $responses;
    }

    /**
     * @return array
     */
    public static function list(): array
    {
        $listeners = [];
        foreach (static::$eventMap as $event_name => $callback_items) {
            foreach ($callback_items as $id => $callback_item) {
                $listeners[$id] = [$event_name, $callback_item];
            }
        }
        foreach (static::$prefixEventMap as $event_name => $callback_items) {
            foreach ($callback_items as $id => $callback_item) {
                $listeners[$id] = [$event_name . '*', $callback_item];
            }
        }
        ksort($listeners);
        return $listeners;
    }

    /**
     * @param mixed $event_name
     * @return callable[]
     */
    public static function getListeners(mixed $event_name): array
    {
        $listeners = static::$eventMap[$event_name] ?? [];
        foreach (static::$prefixEventMap as $name => $callback_items) {
            if (str_starts_with($event_name, $name)) {
                $listeners = array_merge($listeners, $callback_items);
            }
        }
        ksort($listeners);
        return $listeners;
    }

    /**
     * @param mixed $event_name
     * @return bool
     */
    public static function hasListener(mixed $event_name): bool
    {
        return !empty(static::getListeners($event_name));
    }
}