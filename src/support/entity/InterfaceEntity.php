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

namespace support\entity;

interface InterfaceEntity
{
    /**
     * @param array $raw
     * @return void
     */
    public function __construct(array $raw);



    /**
     * Обновление сущности
     * @localzet updateEntity
     * @param string|array $by key | [key => value]
     * @param array $data [key => value]
     * @return bool
     */
    public function update(string|array $by = 'id', array $data = []): bool;

    /**
     * Удаление сущности
     * @localzet deleteEntity
     * @param string|array $by key | [key => value]
     * @return bool
     */
    public function delete(string|array $by = 'id'): bool;



    /**
     * Установка значений
     * @localzet setProperty(s)
     * @param array $data [key => value]
     * @return void
     */
    public function set(array $data): void;

    /**
     * Получение значений
     * @localzet getProperty(s)
     * @param string $keys key1, key2, key3
     * @return array [key1 => value1, key2 => value2, key3 => value3]
     */
    public function get(string ...$keys): array;

    /**
     * Изменение значений
     * @localzet editProperty(s)
     * @param array $data [key => value]
     * @return bool
     */
    public function edit(array $data): bool;

    /**
     * Удаление значений
     * @localzet removeProperty(s)
     * @param string $keys key1, key2, key3
     * @return void
     */
    public function remove(string ...$keys): void;
}
