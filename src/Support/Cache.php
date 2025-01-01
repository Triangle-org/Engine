<?php declare(strict_types=1);

/**
 * @package     Triangle Engine (FrameX Project)
 * @link        https://github.com/Triangle-org/Engine Triangle Engine (v2+)
 * @link        https://github.com/localzet-archive/FrameX-Public FrameX (v1-2)
 *
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright (c) 2023-2025 Triangle Framework Team
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

use InvalidArgumentException;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\PdoAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Psr16Cache;

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
     * @var ?Psr16Cache[] $instance Экземпляр кэша.
     */
    public static ?array $instances = [];

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
    public static function __callStatic(string $name, array $arguments)
    {
        return static::store()->{$name}(...$arguments);
    }

    /**
     * Получить экземпляр кэша.
     * Если экземпляр кэша еще не создан, он будет создан и сохранен в статическом свойстве $instance.
     *
     *
     * @link https://www.php-fig.org/psr/psr-16/
     */
    public static function store(?string $name = null): Psr16Cache
    {
        $name = $name ?: config('cache.default', 'redis');
        $stores = config('cache') ? config('cache.stores', []) : [
            'redis' => [
                'driver' => 'redis',
                'connection' => 'default'
            ],
        ];
        if (!isset($stores[$name])) {
            throw new InvalidArgumentException("cache.store.$name is not defined. Please check config/cache.php");
        }

        if (!isset(static::$instances[$name])) {
            $driver = $stores[$name]['driver'];
            switch ($driver) {
                case 'redis':
                    $client = Redis::connection($stores[$name]['connection'])->client();
                    $adapter = new RedisAdapter($client);
                    break;
                case 'file':
                    $adapter = new FilesystemAdapter('', 0, $stores[$name]['path']);
                    break;
                case 'array':
                    $adapter = new ArrayAdapter(0, $stores[$name]['serialize'] ?? false, 0, 0);
                    break;
                /**
                 * Pdo can not reconnect when the connection is lost. So we can not use pdo as cache.
                 */
                /*case 'database':
                    $adapter = new PdoAdapter(Db::connection($stores[$name]['connection'])->getPdo());
                    break;*/
                default:
                    throw new InvalidArgumentException("cache.store.$name.driver=$driver is not supported.");
            }

            static::$instances[$name] = new Psr16Cache($adapter);
        }

        return static::$instances[$name];
    }
}
