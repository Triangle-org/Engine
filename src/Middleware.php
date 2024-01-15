<?php

/**
 * @package     Triangle Engine (FrameX Project)
 * @link        https://github.com/localzet/FrameX      FrameX Project v1-2
 * @link        https://github.com/Triangle-org/Engine  Triangle Engine v2+
 *
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright (c) 2018-2024 Localzet Group
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

use RuntimeException;
use function array_merge;
use function array_reverse;
use function is_array;
use function method_exists;

/**
 * Класс Middleware
 * Этот класс представляет собой контейнер для промежуточного ПО (Middleware).
 */
class Middleware
{
    /**
     * @var array Массив экземпляров промежуточного ПО
     */
    protected static array $instances = [];

    /**
     * Загружает промежуточное ПО.
     *
     * @param array $list Массив конфигурации промежуточного ПО
     * @param string $plugin Имя плагина (необязательно)
     * @return void
     * @throws RuntimeException Если конфигурация промежуточного ПО некорректна
     */
    public static function load(array $list, string $plugin = ''): void
    {
        foreach ($list as $app => $middlewares) {
            if (!is_array($middlewares)) {
                throw new RuntimeException('Некорректная конфигурация промежуточного ПО');
            }
            if ($app === '@') {
                $plugin = '';
            }
            if (str_contains($app, 'plugin.')) {
                $explode = explode('.', $app, 4);
                $plugin = $explode[1];
                $app = $explode[2] ?? '';
            }
            foreach ($middlewares as $class) {
                if (method_exists($class, 'process')) {
                    static::$instances[$plugin][$app][] = [$class, 'process'];
                } else {
                    throw new RuntimeException("Промежуточный $class::process не существует");
                }
            }
        }
    }

    /**
     * Возвращает промежуточное ПО для указанного плагина и приложения.
     *
     * @param string $plugin Имя плагина
     * @param string $app Имя приложения
     * @param bool $withGlobal Флаг, указывающий, включать ли глобальное промежуточное ПО
     * @return array|mixed Массив промежуточного ПО
     */
    public static function getMiddleware(string $plugin, string $app, bool $withGlobal = true): mixed
    {
        // Глобальное промежуточное ПО
        $globalMiddleware = static::$instances['']['@'] ?? [];
        $appGlobalMiddleware = $withGlobal && isset(static::$instances[$plugin]['']) ? static::$instances[$plugin][''] : [];

        if ($app === '') {
            return array_reverse(array_merge($globalMiddleware, $appGlobalMiddleware));
        }
        // Промежуточное ПО для приложения
        $appMiddleware = static::$instances[$plugin][$app] ?? [];
        return array_reverse(array_merge($globalMiddleware, $appGlobalMiddleware, $appMiddleware));
    }
}
