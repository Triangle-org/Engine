<?php

/**
 * @package     Triangle Engine (FrameX Project)
 * @link        https://github.com/localzet/FrameX      FrameX Project v1-2
 * @link        https://github.com/Triangle-org/Engine  Triangle Engine v2+
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

namespace Triangle\Engine\Route;

use Triangle\Engine\Route as Router;
use function array_merge;
use function count;
use function preg_replace_callback;
use function str_replace;


/**
 * Class Route
 */
class Route
{
    /**
     * @var string|null
     */
    protected ?string $name = null;

    /**
     * @var array
     */
    protected array $methods = [];

    /**
     * @var string
     */
    protected string $path = '';

    /**
     * @var callable
     */
    protected $callback = null;

    /**
     * @var array
     */
    protected array $middlewares = [];

    /**
     * @var array
     */
    protected array $params = [];

    /**
     * Route constructor.
     * @param array $methods
     * @param string $path
     * @param callable $callback
     */
    public function __construct(array $methods, string $path, callable $callback)
    {
        $this->methods = $methods;
        $this->path = $path;
        $this->callback = $callback;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name ?? null;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function name(string $name): Route
    {
        $this->name = $name;
        Router::setByName($name, $this);
        return $this;
    }

    /**
     * @param mixed|null $middleware
     * @return $this|array
     */
    public function middleware(mixed $middleware = null): array|static
    {
        if ($middleware === null) {
            return $this->middlewares;
        }
        $this->middlewares = array_merge($this->middlewares, is_array($middleware) ? $middleware : [$middleware]);
        return $this;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return array
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * @return callable|null
     */
    public function getCallback(): ?callable
    {
        return $this->callback;
    }

    /**
     * @return array
     */
    public function getMiddleware(): array
    {
        return $this->middlewares;
    }

    /**
     * @param string|null $name
     * @param $default
     * @return array|mixed|null
     */
    public function param(string $name = null, $default = null): mixed
    {
        if ($name === null) {
            return $this->params;
        }
        return $this->params[$name] ?? $default;
    }

    /**
     * @param array $params
     * @return $this
     */
    public function setParams(array $params): Route
    {
        $this->params = array_merge($this->params, $params);
        return $this;
    }

    /**
     * @param array $parameters
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
