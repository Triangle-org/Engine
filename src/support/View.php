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

namespace support;

use Triangle\Engine\App;
use function config;

/**
 * Класс View
 * Этот класс предоставляет статические методы для работы с представлениями.
 *
 * @link https://symfony.com/doc/current/templates.html
 */
class View
{
    /**
     * Метод для присвоения значения переменной представления.
     * Этот метод используется для передачи данных из вашего приложения в представление.
     *
     * @param mixed $name Имя переменной.
     * @param mixed|null $value Значение переменной.
     * @return void
     *
     * @link https://symfony.com/doc/current/templates.html#template-variables
     */
    public static function assign(mixed $name, mixed $value = null): void
    {
        $request = App::request();
        $plugin = $request->plugin ?? '';
        $handler = config($plugin ? "plugin.$plugin.view.handler" : 'view.handler');
        $handler::assign($name, $value);
    }

    /**
     * Метод для получения всех переменных представления.
     * Этот метод возвращает массив всех переменных, которые были переданы в представление.
     *
     * @return array Массив переменных представления.
     *
     * @link https://symfony.com/doc/current/templates.html#template-variables
     */
    public static function vars(): array
    {
        $request = App::request();
        $plugin = $request->plugin ?? '';
        $handler = config($plugin ? "plugin.$plugin.view.handler" : 'view.handler');
        return $handler::vars();
    }
}
