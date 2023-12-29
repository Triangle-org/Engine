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

use Exception;
use Triangle\Engine\App;
use Triangle\Engine\Config;

/**
 * Класс Container
 * Этот класс предоставляет статические методы для работы с контейнером зависимостей.
 *
 * @link https://www.php-fig.org/psr/psr-11/
 *
 * Методы:
 * @method static mixed get(string $name) Получает значение из контейнера по ключу.
 * @method static mixed make(string $name, array $parameters = []) Создает новый экземпляр класса, указанного в $name, с переданными параметрами.
 * @method static bool has(string $name) Проверяет, есть ли значение в контейнере по ключу.
 */
class Container
{
    /**
     * Магический метод для вызова методов контейнера.
     *
     * @param string $name Имя метода.
     * @param array $arguments Аргументы метода.
     *
     * @return mixed Результат вызова метода.
     *
     * @throws Exception Если метод не существует в контейнере.
     *
     * @link https://www.php.net/manual/ru/language.oop5.overloading.php#object.callstatic
     */
    public static function __callStatic(string $name, array $arguments)
    {
        $plugin = App::getPluginByClass($name);
        $container = static::instance($plugin);
        if (!method_exists($container, $name)) {
            throw new Exception("Метод $name не существует в контейнере");
        }

        return call_user_func_array([$container, $name], $arguments);
    }

    /**
     * Получить экземпляр контейнера.
     * Если экземпляр контейнера еще не создан, он будет создан и сохранен в статическом свойстве $instance.
     *
     * @param string $plugin Плагин, которому принадлежит контейнер (необязательно).
     *
     * @return array|mixed|void|null
     *
     * @link https://www.php-fig.org/psr/psr-11/
     */
    public static function instance(string $plugin = '')
    {
        return Config::get($plugin ? "plugin.$plugin.container" : 'container');
    }
}
