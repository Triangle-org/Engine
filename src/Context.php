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

namespace Triangle\Engine;

use Fiber;
use localzet\Server;
use localzet\Server\Events\Linux;
use localzet\Server\Events\Swoole;
use localzet\Server\Events\Swow;
use SplObjectStorage;
use StdClass;
use WeakMap;
use function property_exists;

/**
 * Класс Context
 * Этот класс предоставляет методы для работы с контекстом выполнения.
 * Подробнее о контекстах выполнения можно прочитать здесь.
 */
class Context
{
    protected static ?WeakMap $objectStorage = null;

    protected static StdClass $object;

    /**
     * Получить значение из контекста
     * @param string|null $key Ключ значения
     */
    public static function get(?string $key = null): mixed
    {
        return $key ? static::getObject()?->$key : static::getObject();
    }

    /**
     * Получить объект контекста
     */
    public static function init(): void
    {
        if (!(static::$objectStorage instanceof WeakMap)) {
            static::$objectStorage = class_exists(WeakMap::class) ? new WeakMap() : new SplObjectStorage();
            static::$object = new StdClass;
        }
    }

    protected static function getObject(): StdClass|null
    {
        /** @var Fiber $key */
        $key = static::getKey();
        if ($key && !isset(static::$objectStorage[$key])) {
            static::$objectStorage[$key] = new StdClass;
        }

        return $key ? static::$objectStorage[$key] : null;
    }

    /**
     * Получить ключ контекста
     */
    protected static function getKey(): object
    {
        return match (Server::$eventLoopClass) {
            Linux::class => Fiber::getCurrent(),
            Swoole::class => \Swoole\Coroutine::getContext(),
            Swow::class => \Swow\Coroutine::getCurrent(),
            default => static::$object,
        };
    }

    /**
     * Установить значение в контекст
     * @param string $key Ключ значения
     * @param mixed $value Значение
     */
    public static function set(string $key, mixed $value): void
    {
        if (($obj = static::getObject()) instanceof StdClass) {
            $obj->$key = $value;
        }
    }

    /**
     * Удалить значение из контекста
     * @param string $key Ключ значения
     */
    public static function delete(string $key): void
    {
        if (($obj = static::getObject()) instanceof StdClass) {
            unset($obj->$key);
        }
    }

    /**
     * Проверить наличие значения в контексте
     * @param string $key Ключ значения
     */
    public static function has(string $key): bool
    {
        return $key && ($obj = static::getObject()) && property_exists($obj, $key);
    }

    /**
     * Уничтожить контекст
     */
    public static function destroy(): void
    {
        if ($key = static::getKey()) {
            unset(static::$objectStorage[$key]);
        }
    }
}
