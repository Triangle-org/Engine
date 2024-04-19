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

namespace Triangle\Engine;

use Closure;
use Dotenv\Dotenv;
use Dotenv\Repository\Adapter\PutenvAdapter;
use Dotenv\Repository\RepositoryBuilder;
use Dotenv\Repository\RepositoryInterface;
use PhpOption\Option;
use PhpOption\Some;
use RuntimeException;

class Environment
{
    /**
     * Указывает, включен ли адаптер putenv.
     *
     * @var bool
     */
    protected static bool $putenv = true;

    /**
     * Экземпляр репозитория окружения.
     *
     * @var RepositoryInterface|null
     */
    protected static ?RepositoryInterface $repository = null;

    public static function load(string $environmentPath, string $environmentFile = '.env'): void
    {
        if (
            class_exists(Dotenv::class)
            && is_dir($environmentPath)
            && file_exists(rtrim($environmentPath) . '/' . ltrim($environmentFile))
        ) {
            Dotenv::create(
                self::getRepository(),
                $environmentPath,
                $environmentFile
            );
        }
    }

    /**
     * Включить адаптер putenv.
     *
     * @return void
     */
    public static function enablePutenv(): void
    {
        self::$putenv = true;
        self::$repository = null;
    }

    /**
     * Отключить адаптер putenv.
     *
     * @return void
     */
    public static function disablePutenv(): void
    {
        self::$putenv = false;
        self::$repository = null;
    }

    /**
     * Получить экземпляр репозитория окружения.
     *
     * @return RepositoryInterface|null
     */
    public static function getRepository(): ?RepositoryInterface
    {
        if (self::$repository === null) {
            $builder = RepositoryBuilder::createWithDefaultAdapters();

            if (self::$putenv) {
                $builder = $builder->addAdapter(PutenvAdapter::class);
            }

            self::$repository = $builder->immutable()->make();
        }

        return self::$repository;
    }

    /**
     * Получить значение переменной окружения.
     *
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return self::getOption($key)->getOrCall(fn() => $default instanceof Closure ? $default() : $default);
    }

    /**
     * Получить значение обязательной переменной окружения.
     *
     * @param string $key
     * @return mixed
     *
     * @throws RuntimeException
     */
    public static function getOrFail(string $key): mixed
    {
        return self::getOption($key)->getOrThrow(new RuntimeException("Переменная окружения [$key] не имеет значения."));
    }

    /**
     * Получить возможный вариант для этой переменной окружения.
     *
     * @param string $key
     * @return Option|Some
     */
    protected static function getOption(string $key): Some|Option
    {

        return Option::fromValue(self::getRepository()->get($key))
            ->map(function ($value) {
                $valueMap = [
                    'true' => true,
                    'false' => false,
                    'null' => null,
                    'empty' => '',
                ];
                $value = strtolower($value);

                if (isset($valueMap[$value]) || isset($valueMap["($value)"])) {
                    return $valueMap[$value] ?? $valueMap["($value)"];
                }

                if (preg_match('/\A([\'"])(.*)\1\z/', $value, $matches)) {
                    return $matches[2];
                }

                return $value;
            });
    }
}