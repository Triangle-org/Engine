<?php declare(strict_types=1);

/**
 * @package     Triangle Engine (FrameX Project)
 * @link        https://github.com/Triangle-org/Engine Triangle Engine (v2+)
 * @link        https://github.com/localzet-archive/FrameX-Public FrameX (v1-2)
 *
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright (c) 2023-2024 Triangle Framework Team
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

namespace Triangle\Engine;

use Psr\Container\{ContainerInterface, NotFoundExceptionInterface};
use Triangle\Engine\Exception\NotFoundException;
use function array_key_exists;
use function class_exists;

/**
 * Класс Container
 * Этот класс реализует интерфейс ContainerInterface и предоставляет методы для работы с контейнером зависимостей.
 */
class Container implements ContainerInterface
{
    /**
     * Массив для хранения экземпляров зарегистрированных зависимостей.
     *
     * @var array
     */
    protected array $instances = [];

    /**
     * Массив для хранения определений зависимостей.
     *
     * @var array
     */
    protected array $definitions = [];

    /**
     * Находит запись контейнера по ее идентификатору и возвращает его.
     *
     * @param string $id Идентификатор записи для поиска.
     *
     * @return mixed Запись.
     * @throws NotFoundExceptionInterface  Если для данного идентификатора запись не найдена.
     */
    public function get(string $id): mixed
    {
        if (!isset($this->instances[$id])) {
            if (isset($this->definitions[$id])) {
                $this->instances[$id] = call_user_func($this->definitions[$id], $this);
            } else {
                if (!class_exists($id)) {
                    throw new NotFoundException("Класс '$id' не найден");
                }
                $this->instances[$id] = new $id();
            }
        }
        return $this->instances[$id];
    }

    /**
     * Возвращает true, если контейнер может вернуть запись для данного идентификатора.
     *  В противном случае возвращает false.
     *
     * `has($id)`, возвращающее true, не означает, что `get($id)` не вызовет исключение.
     * Однако это означает, что `get($id)` не будет вызывать `NotFoundExceptionInterface`.
     *
     * @param string $id Идентификатор записи для поиска.
     *
     * @return bool
     */
    public function has(string $id): bool
    {
        return array_key_exists($id, $this->instances)
            || array_key_exists($id, $this->definitions);
    }

    /**
     * Создает новый экземпляр класса с заданными параметрами конструктора.
     *
     * @param string $name Имя класса.
     * @param array $constructor Параметры конструктора.
     *
     * @return mixed Новый экземпляр класса.
     * @throws NotFoundException Если класс не найден.
     */
    public function make(string $name, array $constructor = []): mixed
    {
        if (!class_exists($name)) {
            throw new NotFoundException("Класс '$name' не найден");
        }
        return new $name(...array_values($constructor));
    }

    /**
     * Добавляет определения в контейнер.
     *
     * @param array $definitions Определения для добавления.
     *
     * @return $this
     */
    public function addDefinitions(array $definitions): Container
    {
        $this->definitions = array_merge($this->definitions, $definitions);
        return $this;
    }
}