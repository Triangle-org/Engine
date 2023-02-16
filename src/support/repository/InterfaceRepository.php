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

namespace support\repository;

use support\entity\InterfaceEntity;

interface InterfaceRepository
{
    /**
     * Сущности
     */

    /**
     * Создать
     * 
     * @param array|InterfaceEntity $data Данные в виде массива или сущности
     * @return bool
     */
    public static function createEntity(array|InterfaceEntity $data): bool;


    /**
     * Получить
     * 
     * @param array $where Массив условий ['field' => 'value']
     * @param array $params Дополнительные свойства к сущности
     * @param bool $entity Упаковывать в сущности?
     * @param bool $multi true = get(), false = getOne()
     * @param array $func Дополнительная обработка функцией из \support\database\MySQL
     * @param string $operator Оператор условий ('=', 'LIKE')
     * @param string $cond Для нескольких условий (OR, AND)
     * @param int|null $numRows Лимит [$offset, $count] или $count
     * @param string $columns Выборка столбцов
     * @return InterfaceEntity|array
     */
    public static function getEntity(
        array $where = [],
        array $params = [],
        bool $entity = true,
        bool $multi = true,

        // where
        array $func = [],
        string $operator = '=',
        string $cond = 'AND',

        // get/getOne
        int|null $numRows = null, // Лимит ($offset, $count)
        string $columns = '*',
    );

    /**
     * Обновить
     * 
     * @param array $input Массив массивов условий и данных ['where' => ['field' => 'value'], 'data' => [key => value, ...]]
     * @param array $func Дополнительная обработка функцией из \support\database\MySQL
     * @param string $operator Оператор условий ('=', 'LIKE')
     * @param string $cond Для нескольких условий (OR, AND)
     * @return bool
     */
    public static function updateEntity(
        array $input,
        bool $multi = false,

        // where
        array $func = [],
        string $operator = '=',
        string $cond = 'AND',
    ): bool;

    /**
     * Удалить
     * 
     * @param array $input Массив массивов условий [['field' => 'value']]
     * @param array $func Дополнительная обработка функцией из \support\database\MySQL
     * @param string $operator Оператор условий ('=', 'LIKE')
     * @param string $cond Для нескольких условий (OR, AND)
     * @return bool
     */
    public static function deleteEntity(
        array $input,
        bool $multi = false,

        // where
        array $func = [],
        string $operator = '=',
        string $cond = 'AND',
    ): bool;
}
