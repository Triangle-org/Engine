<?php

/**
 * @package     Triangle Engine
 * @link        https://github.com/Triangle-org/Engine Triangle Engine (v2+)
 * @link        https://github.com/localzet-archive/FrameX-Public FrameX (v1-2)
 *
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright (c) 2018-2024 Zorin Projects S.P.
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
 *              For any questions, please contact <creator@localzet.com>
 */

namespace Triangle\Engine;

use Fiber;
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
    /**
     * @var WeakMap|SplObjectStorage|null
     */
    protected static WeakMap|null|SplObjectStorage $objectStorage = null;

    /**
     * Получить значение из контекста
     * @param string|null $key Ключ значения
     * @return mixed
     */
    public static function get(string $key = null): mixed
    {
        $obj = static::getObject();
        if ($key === null) {
            return $obj;
        }
        return $obj->$key ?? null;
    }

    /**
     * Получить объект контекста
     * @return StdClass
     */
    protected static function getObject(): StdClass
    {
        if (!static::$objectStorage) {
            static::$objectStorage = class_exists(WeakMap::class) ? new WeakMap() : new SplObjectStorage();
        }
        $key = static::getKey();
        if (!isset(static::$objectStorage[$key])) {
            static::$objectStorage[$key] = new StdClass;
        }
        return static::$objectStorage[$key];
    }

    /**
     * Получить ключ контекста
     * @return Fiber|null
     */
    protected static function getKey(): ?Fiber
    {
        return Fiber::getCurrent();
    }

    /**
     * Установить значение в контекст
     * @param string $key Ключ значения
     * @param mixed $value Значение
     * @return void
     */
    public static function set(string $key, mixed $value): void
    {
        $obj = static::getObject();
        $obj->$key = $value;
    }

    /**
     * Удалить значение из контекста
     * @param string $key Ключ значения
     * @return void
     */
    public static function delete(string $key): void
    {
        $obj = static::getObject();
        unset($obj->$key);
    }

    /**
     * Проверить наличие значения в контексте
     * @param string $key Ключ значения
     * @return bool
     */
    public static function has(string $key): bool
    {
        $obj = static::getObject();
        return property_exists($obj, $key);
    }

    /**
     * Уничтожить контекст
     * @return void
     */
    public static function destroy(): void
    {
        unset(static::$objectStorage[static::getKey()]);
    }
}
