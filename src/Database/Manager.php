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

namespace Triangle\Engine\Database;

use Closure;

/**
 * Класс Manager
 * Этот класс предоставляет статические методы для работы с базой данных.
 *
 * @link https://laravel.com/docs/8.x/database
 *
 * Методы:
 * @method static array select(string $query, $bindings = [], $useReadPdo = true) Выполняет SELECT-запрос в базе данных и возвращает результат.
 * @method static int insert(string $query, $bindings = []) Выполняет INSERT-запрос в базе данных и возвращает количество затронутых строк.
 * @method static int update(string $query, $bindings = []) Выполняет UPDATE-запрос в базе данных и возвращает количество затронутых строк.
 * @method static int delete(string $query, $bindings = []) Выполняет DELETE-запрос в базе данных и возвращает количество затронутых строк.
 * @method static bool statement(string $query, $bindings = []) Выполняет SQL-запрос в базе данных и возвращает true в случае успеха и false в случае неудачи.
 * @method static mixed transaction(Closure $callback, $attempts = 1) Выполняет транзакцию в базе данных.
 * @method static void beginTransaction() Начинает транзакцию в базе данных.
 * @method static void rollBack($toLevel = null) Откатывает транзакцию в базе данных.
 * @method static void commit() Фиксирует транзакцию в базе данных.
 */
class Manager extends \Illuminate\Database\Capsule\Manager
{
}
