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

use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Triangle\Engine\Redis\RedisManager;

/**
 * Класс Cache
 * Этот класс предоставляет методы для работы с кэшем.
 *
 * @link https://www.php-fig.org/psr/psr-16/
 *
 * Методы:
 * @method static mixed get($key, $default = null) Получает значение из кэша по ключу.
 * @method static bool set($key, $value, $ttl = null) Сохраняет значение в кэше по ключу.
 * @method static bool delete($key) Удаляет значение из кэша по ключу.
 * @method static bool clear() Очищает весь кэш.
 * @method static iterable getMultiple($keys, $default = null) Получает несколько значений из кэша по массиву ключей.
 * @method static bool setMultiple($values, $ttl = null) Сохраняет несколько значений в кэше.
 * @method static bool deleteMultiple($keys) Удаляет несколько значений из кэша по массиву ключей.
 * @method static bool has($key) Проверяет, есть ли значение в кэше по ключу.
 */
class Cache
{
    /**
     * @var Psr16Cache|null $instance Экземпляр кэша.
     */
    public static ?Psr16Cache $instance = null;

    /**
     * Магический метод для вызова методов кэша.
     *
     * @param string $name Имя метода.
     * @param array $arguments Аргументы метода.
     *
     * @return mixed Результат вызова метода.
     *
     * @link https://www.php.net/manual/ru/language.oop5.overloading.php#object.callstatic
     */
    public static function __callStatic($name, $arguments)
    {
        return static::instance()->{$name}(...$arguments);
    }

    /**
     * Получить экземпляр кэша.
     * Если экземпляр кэша еще не создан, он будет создан и сохранен в статическом свойстве $instance.
     *
     * @return Psr16Cache|null
     *
     * @link https://www.php-fig.org/psr/psr-16/
     */
    public static function instance(): ?Psr16Cache
    {
        if (!static::$instance) {
            $adapter = new RedisAdapter(RedisManager::connection()->client());
            self::$instance = new Psr16Cache($adapter);
        }
        return static::$instance;
    }
}
