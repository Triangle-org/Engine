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

namespace Triangle\Engine\Router;

use Triangle\Engine\Router;
use function array_merge;
use function count;
use function preg_replace_callback;
use function str_replace;


/**
 * Класс Route представляет собой маршрут в веб-приложении.
 * @package Triangle\Engine\Router
 */
class Route
{
    /** @var string|null $name Имя маршрута */
    protected ?string $name = null;

    /** @var array $methods HTTP-методы маршрута */
    protected array $methods = [];

    /** @var string $path Путь маршрута */
    protected string $path = '';

    /** @var callable|mixed $callback Обработчик маршрута */
    protected $callback = null;

    /** @var array $middlewares Промежуточное ПО маршрута */
    protected array $middlewares = [];

    /** @var array $params Параметры маршрута */
    protected array $params = [];

    /**
     * Конструктор маршрута.
     * @param array $methods HTTP-методы
     * @param string $path Путь маршрута
     * @param callable|mixed $callback Обработчик маршрута
     */
    public function __construct(array $methods, string $path, mixed $callback)
    {
        $this->methods = $methods;
        $this->path = $path;
        $this->callback = $callback;
    }

    /**
     * Получить имя маршрута.
     * @return string|null Возвращает имя маршрута или null, если имя не задано.
     */
    public function getName(): ?string
    {
        return $this->name ?? null;
    }

    /**
     * Установить имя маршрута.
     * @param string $name Имя маршрута.
     * @return static Возвращает текущий объект Route для цепочки вызовов.
     */
    public function name(string $name): Route
    {
        $this->name = $name;
        Router::setByName($name, $this);
        return $this;
    }

    /**
     * Получить или установить промежуточное ПО.
     * @param mixed|null $middleware Промежуточное ПО.
     * @return static|array Возвращает массив промежуточного ПО, если $middleware равен null. В противном случае возвращает текущий объект Route.
     */
    public function middleware(mixed $middleware = null): array|static
    {
        if ($middleware === null) {
            return $this->middlewares;
        }
        $this->middlewares = array_merge($this->middlewares, is_array($middleware) ? array_reverse($middleware) : [$middleware]);
        return $this;
    }

    /**
     * Получить путь маршрута.
     * @return string Возвращает путь маршрута.
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Получить методы маршрута.
     * @return array Возвращает массив HTTP-методов маршрута.
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * Получить обработчик маршрута.
     * @return callable|mixed|null Возвращает обработчик маршрута.
     */
    public function getCallback(): mixed
    {
        return $this->callback;
    }

    /**
     * Получить промежуточное ПО маршрута
     * @return array
     */
    public function getMiddleware(): array
    {
        return $this->middlewares;
    }

    /**
     * Получить параметры маршрута
     * @param string|null $name Имя параметра
     * @param mixed $default Значение по умолчанию
     * @return array|mixed|null
     */
    public function param(string $name = null, mixed $default = null): mixed
    {
        if ($name === null) {
            return $this->params;
        }
        return $this->params[$name] ?? $default;
    }

    /**
     * Установить параметры маршрута
     * @param array $params Параметры
     * @return static
     */
    public function setParams(array $params): Route
    {
        $this->params = array_merge($this->params, $params);
        return $this;
    }

    /**
     * Получить URL маршрута
     * @param array $parameters Параметры
     * @return string
     */
    public function url(array $parameters = []): string
    {
        if (empty($parameters)) {
            return $this->path;
        }
        $path = str_replace(['[', ']'], '', $this->path);
        $path = preg_replace_callback('/\{(.*?)(?::[^}]*?)*?}/', function ($matches) use (&$parameters) {
            if (!$parameters) {
                return $matches[0];
            }
            if (isset($parameters[$matches[1]])) {
                $value = $parameters[$matches[1]];
                unset($parameters[$matches[1]]);
                return $value;
            }
            $key = key($parameters);
            if (is_int($key)) {
                $value = $parameters[$key];
                unset($parameters[$key]);
                return $value;
            }
            return $matches[0];
        }, $path);
        return count($parameters) > 0 ? $path . '?' . http_build_query($parameters) : $path;
    }
}
