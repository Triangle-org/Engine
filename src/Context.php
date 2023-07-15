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

namespace Triangle\Engine;

use Fiber;
use localzet\Server\Events\Revolt;
use localzet\Server\Events\Swoole;
use localzet\Server\Events\Swow;
use localzet\Server;
use SplObjectStorage;
use StdClass;
use Swow\Coroutine;
use WeakMap;
use function property_exists;

/**
 * Class Context
 */
class Context
{

    /**
     * @var WeakMap|SplObjectStorage|null
     */
    protected static WeakMap|null|SplObjectStorage $objectStorage = null;

    /**
     * @var StdClass
     */
    protected static StdClass $object;

    /**
     * @return StdClass
     */
    protected static function getObject(): StdClass
    {
        if (!static::$objectStorage) {
            static::$objectStorage = class_exists(WeakMap::class) ? new WeakMap() : new SplObjectStorage();
            static::$object = new StdClass;
        }
        $key = static::getKey();
        if (!isset(static::$objectStorage[$key])) {
            static::$objectStorage[$key] = new StdClass;
        }
        return static::$objectStorage[$key];
    }

    /**
     * @return mixed
     */
    protected static function getKey(): mixed
    {
        return match (Server::$eventLoopClass) {
            Revolt::class => Fiber::getCurrent(),
            Swoole::class => \Swoole\Coroutine::getContext(),
            Swow::class => Coroutine::getCurrent(),
            default => static::$object,
        };
    }

    /**
     * @param string|null $key
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
     * @param string $key
     * @param $value
     * @return void
     */
    public static function set(string $key, $value): void
    {
        $obj = static::getObject();
        $obj->$key = $value;
    }

    /**
     * @param string $key
     * @return void
     */
    public static function delete(string $key): void
    {
        $obj = static::getObject();
        unset($obj->$key);
    }

    /**
     * @param string $key
     * @return bool
     */
    public static function has(string $key): bool
    {
        $obj = static::getObject();
        return property_exists($obj, $key);
    }

    /**
     * @return void
     */
    public static function destroy(): void
    {
        unset(static::$objectStorage[static::getKey()]);
    }
}
