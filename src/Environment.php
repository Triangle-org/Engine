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

use Closure;
use Dotenv\Dotenv;
use Dotenv\Repository\Adapter\PutenvAdapter;
use Dotenv\Repository\RepositoryBuilder;
use Dotenv\Repository\RepositoryInterface;
use localzet\Server;
use PhpOption\Option;
use PhpOption\Some;
use RuntimeException;

class Environment implements BootstrapInterface
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

    public static function start(?Server $server = null): void
    {
        self::load(run_path());
    }

    public static function load(string $path, $file = '.env'): void
    {
        if (
            class_exists(Dotenv::class)
            && file_exists(path_combine($path, $file))
        ) {
            Dotenv::create(self::getRepository(), $path, $file)->safeLoad();
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
     * Изменить значение переменной окружения.
     *
     * @param array $values
     * @param string $environmentFile
     * @return false|int
     */
    public static function set(array $values, string $environmentFile = '.env'): false|int
    {
        $envFile = file_exists($environmentFile) ? $environmentFile : run_path($environmentFile);
        $str = file_get_contents($envFile);

        if (count($values) > 0) {
            foreach ($values as $envKey => $envValue) {
                $str .= "\n"; // Если искомая переменная находится в последней строке без \n
                $keyPosition = strpos($str, "$envKey=");

                if ($keyPosition) {
                    $endOfLinePosition = strpos($str, "\n", $keyPosition);
                    $oldLine = substr($str, $keyPosition, $endOfLinePosition - $keyPosition);
                    $str = str_replace($oldLine, "$envKey=\"$envValue\"", $str);
                } else {
                    $str .= "\n$envKey=\"$envValue\"";
                }
            }
        }

        $str = substr($str, 0, -1);

        return file_put_contents($envFile, $str);
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

                if (isset($valueMap[strtolower($value)])) {
                    return $valueMap[strtolower($value)];
                }

                if (is_numeric($value)) {
                    return $value + 0;
                }

                if (preg_match('/\A([\'"])(.*)\1\z/', $value, $matches)) {
                    return $matches[2];
                }

                return $value;
            });
    }
}