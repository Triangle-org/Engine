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

use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Class Events
 *
 * Класс для работы с событиями.
 */
class Events
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
     * Метод для регистрации обработчика события.
     *
     * @param string $event_name Имя события.
     * @param callable $listener Обработчик события.
     * @return int ID обработчика события.
     */
    public static function on(string $event_name, callable $listener): int
    {
        $is_prefix_name = str_ends_with($event_name, '*');
        if ($is_prefix_name) {
            static::$prefixEventMap[substr($event_name, 0, -1)][++static::$id] = $listener;
        } else {
            static::$eventMap[$event_name][++static::$id] = $listener;
        }
        return static::$id;
    }

    /**
     * Метод для удаления обработчика события.
     *
     * @param string $event_name Имя события.
     * @param int $id ID обработчика события.
     * @return int Результат удаления обработчика события.
     */
    public static function off(string $event_name, int $id): int
    {
        if (isset(static::$eventMap[$event_name][$id])) {
            unset(static::$eventMap[$event_name][$id]);
            return 1;
        }
        return 0;
    }

    /**
     * Метод для вызова события.
     *
     * @param string $event_name Имя события.
     * @param mixed $data Данные для обработчика события.
     * @param bool $halt Остановить ли выполнение после первого обработчика.
     * @return mixed Результат вызова события.
     */
    public static function emit(string $event_name, mixed $data, bool $halt = false): mixed
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
     * Метод для получения обработчиков события.
     *
     * @param string $event_name Имя события.
     * @return callable[] Список обработчиков события.
     */
    public static function getListeners(string $event_name): array
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
     * Метод для получения списка всех обработчиков событий.
     *
     * @return array Список всех обработчиков событий.
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
     * Метод для проверки наличия обработчиков события.
     *
     * @param string $event_name Имя события.
     * @return bool Наличие обработчиков события.
     */
    public static function hasListener(string $event_name): bool
    {
        return !empty(static::getListeners($event_name));
    }
}