<?php declare(strict_types=1);

/**
 * @package     Triangle Engine (FrameX Project)
 * @link        https://github.com/Triangle-org/Engine Triangle Engine (v2+)
 * @link        https://github.com/localzet-archive/FrameX-Public FrameX (v1-2)
 *
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright (c) 2023-2025 Triangle Framework Team
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

use Dotenv\Dotenv;
use Dotenv\Repository\RepositoryBuilder;
use Dotenv\Repository\RepositoryInterface;
use RuntimeException;

class Environment
{
    /**
     * Флаг, показывающий, была ли загружена библиотека Dotenv.
     */
    protected static bool $dotenvLoaded = false;

    /**
     * Экземпляр репозитория окружения.
     */
    protected static ?RepositoryInterface $repository = null;

    /**
     * Загрузка переменных среды из файла `.env`, если он существует.
     * Если файл отсутствует, используются системные переменные окружения.
     */
    public static function load(string|array $path, string|array $file = '.env'): void
    {
        if (class_exists(Dotenv::class) && file_exists(path_combine($path, $file))) {
            self::$dotenvLoaded = true;
            Dotenv::create(self::getRepository(), $path, $file)->safeLoad();
        }
    }

    /**
     * Получить значение переменной окружения.
     *
     * @param string $key
     * @param mixed|null $default Значение по умолчанию.
     * @return mixed
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        // Проверяем, загружена ли библиотека Dotenv
        if (self::$dotenvLoaded) {
            $value = self::getRepository()?->get($key);

            // Если значение в `.env` существует, даже пустое, возвращаем его
            if ($value !== null) {
                return $value === '' ? '' : self::parseValue($value);
            }
        }

        // Если переменной нет в `.env`, берем значение из системного окружения
        $value = getenv($key);

        return $value !== false ? self::parseValue($value) : ($default instanceof \Closure ? $default() : $default);
    }

    /**
     * Получить значение обязательной переменной окружения.
     *
     * @throws RuntimeException
     */
    public static function getOrFail(string $key): mixed
    {
        $value = self::get($key);

        if ($value === null) {
            throw new RuntimeException("Переменная окружения [$key] не найдена.");
        }

        return $value;
    }

    /**
     * Получить экземпляр репозитория окружения.
     */
    protected static function getRepository(): RepositoryInterface
    {
        if (self::$repository === null) {
            self::$repository = RepositoryBuilder::createWithDefaultAdapters()->immutable()->make();
        }

        return self::$repository;
    }

    /**
     * Парсинг значения переменной окружения.
     *
     * @param string $value Значение переменной.
     * @return mixed
     */
    protected static function parseValue(string $value): mixed
    {
        $valueMap = [
            'true' => true,
            'false' => false,
            'null' => null,
            'empty' => '',
        ];

        $lowerValue = strtolower(trim($value));

        return $valueMap[$lowerValue] ?? (is_numeric($value) ? $value + 0 : $value);
    }
}
