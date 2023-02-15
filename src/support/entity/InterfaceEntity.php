<?php

/**
 * @package     Triangle Engine
 * @link        https://github.com/Triangle-org/Engine
 * 
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright (c) 2018-2023 Localzet Group
 * @license     https://www.localzet.com/license GNU GPLv3 License
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
