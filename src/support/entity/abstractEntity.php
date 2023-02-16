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

use support\repository\InterfaceRepository;

/**
 * Abstract Entity
 */
abstract class abstractEntity implements InterfaceEntity
{
    static string $repository = '\support\repository\abstractRepository';

    /**
     * @param array $raw
     * @return void
     */
    public function __construct(array $raw)
    {
        if (empty($raw)) {
            throw new exceptionEntity("Пустая сущность", 500);
        }

        $this->set($raw);
    }

    /**
     * Сущность
     */

    /**
     * Обновление сущности
     * @localzet updateEntity
     * @param string|array $where key | [key => value]
     * @param array $data [key => value]
     * @return bool
     */
    public function update(string|array $where = 'id', array $data = []): bool
    {
        $this->set($data);
        $data = $this->get();

        if (is_string($where)) $where = [$where => $this->{$where}];

        // [
        //     'where' => [key => value], 
        //     'data' => [key => value, ...]
        // ]

        /** @var InterfaceRepository $repository */
        $repository = static::$repository;

        return $repository::updateEntity(['where' => $where, 'data' => $data]);
    }

    /**
     * Удаление сущности
     * @localzet deleteEntity
     * @param string|array $where key | [key => value]
     * @return bool
     */
    public function delete(string|array $where = 'id'): bool
    {
        if (is_string($where)) $where = [$where => $this->{$where}];

        // [
        //     [key => value]
        // ]

        /** @var InterfaceRepository $repository */
        $repository = static::$repository;

        return $repository::deleteEntity([$where]);
    }

    /**
     * Свойства
     */

    /**
     * Установка значений
     * @localzet setProperty(s)
     * @param array $data [key => value]
     * @return void
     */
    public function set(array $data): void
    {
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * Получение значений
     * @localzet getProperty(s)
     * @param string $keys key1, key2, key3
     * @return array [key1 => value1, key2 => value2, key3 => value3]
     */
    public function get(string ...$keys): array
    {
        if (empty($keys)) {
            return (array) clone $this;
        }

        $array = [];

        foreach ($keys as $key) {
            $array[$key] = $this->{$key};
        }

        return (array) clone $array;
    }

    /**
     * Изменение значений
     * @localzet editProperty(s)
     * @param array $data [key => value]
     * @return bool
     */
    public function edit(array $data): bool
    {
        $this->set($data);

        foreach ($data as $key => $value) {
            if ($this->{$key} !== $value) return false;
        }

        return true;
    }

    /**
     * Удаление значений
     * @localzet removeProperty(s)
     * @param string $keys key1, key2, key3
     * @return void
     */
    public function remove(string ...$keys): void
    {
        foreach ($keys as $key) {
            unset($this->{$key});
        }
    }

    /**
     * Магические методы
     */

    /**
     * Установка значения
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value)
    {
        $this->set([$key => $value]);
    }

    /**
     * Получение данных
     * @param string $key
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Проверка данных
     * @param string $key
     */
    public function __isset($key)
    {
        return null !== $this->{$key};
    }

    /**
     * Удаление данных
     * @param string $key
     */
    public function __unset($key)
    {
        $this->remove($key);
    }
}
