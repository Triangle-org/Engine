<?php

/**
 * @package     Triangle Engine
 * @link        https://github.com/Triangle-org/Engine
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

use RuntimeException;
use function array_merge;
use function array_reverse;
use function is_array;
use function method_exists;

class Middleware
{
    /**
     * @var array
     */
    protected static array $instances = [];

    /**
     * @param array $allMiddlewares
     * @param string $plugin
     * @return void
     */
    public static function load(array $allMiddlewares, string $plugin = ''): void
    {

        // $allMiddlewares = [
        //     'app1' => [
        //         'Class1',
        //         'Class2',
        //         'Class3'
        //     ],
        //     'app2' => [
        //         'Class1',
        //         'Class2',
        //         'Class3'
        //     ]
        // ];

        foreach ($allMiddlewares as $appName => $middlewares) {
            if (!is_array($middlewares)) {
                throw new RuntimeException('Некорректная конфигурация промежуточного ПО');
            }
            foreach ($middlewares as $className) {
                if (method_exists($className, 'process')) {
                    static::$instances[$plugin][$appName][] = [$className, 'process'];
                } else {
                    // @todo Log
                    echo "Промежуточный $className::process не существует\n";
                }
            }
        }
    }

    /**
     * @param string $plugin
     * @param string $appName
     * @param bool $withGlobalMiddleware
     * @return array|mixed
     */
    public static function getMiddleware(string $plugin, string $appName, bool $withGlobalMiddleware = true): mixed
    {
        // Глобальная midleware
        $globalMiddleware = $withGlobalMiddleware && isset(static::$instances[$plugin]['']) ? static::$instances[$plugin][''] : [];
        if ($appName === '') {
            return array_reverse($globalMiddleware);
        }
        // midleware приложения
        $appMiddleware = static::$instances[$plugin][$appName] ?? [];
        return array_reverse(array_merge($globalMiddleware, $appMiddleware));
    }
}
