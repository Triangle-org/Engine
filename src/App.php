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

namespace Triangle\Engine;

use Closure;
use Exception;
use FastRoute\Dispatcher;
use InvalidArgumentException;
use localzet\Server\Connection\TcpConnection;
use localzet\Server\Protocols\Http;
use localzet\Server;
use Monolog\Logger;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use Throwable;
use Triangle\Engine\Exception\ExceptionHandler;
use Triangle\Engine\Exception\ExceptionHandlerInterface;
use Triangle\Engine\Http\Request;
use Triangle\Engine\Http\Response;
use Triangle\Engine\Route\Route as RouteObject;
use function array_merge;
use function array_pop;
use function array_reduce;
use function array_reverse;
use function array_splice;
use function array_values;
use function class_exists;
use function clearstatcache;
use function count;
use function current;
use function end;
use function explode;
use function get_class_methods;
use function gettype;
use function implode;
use function in_array;
use function is_a;
use function is_array;
use function is_dir;
use function is_file;
use function is_string;
use function key;
use function method_exists;
use function next;
use function ob_get_clean;
use function ob_start;
use function pathinfo;
use function scandir;
use function str_replace;
use function strtolower;
use function substr;
use function trim;

/**
 * Class App
 */
class App
{
    /**
     * @var callable[]
     */
    protected static array $callbacks = [];

    /**
     * @var Server|null
     */
    protected static ?Server $server = null;

    /**
     * @var Logger|null
     */
    protected static ?Logger $logger = null;

    /**
     * @var string
     */
    protected static string $appPath = '';

    /**
     * @var string
     */
    protected static string $publicPath = '';

    /**
     * @var string
     */
    protected static string $requestClass = '';

    /**
     * @param string $requestClass
     * @param Logger $logger
     * @param string $appPath
     * @param string $publicPath
     */
    public function __construct(string $requestClass, Logger $logger, string $appPath, string $publicPath)
    {
        static::$requestClass = $requestClass;
        static::$logger = $logger;
        static::$publicPath = $publicPath;
        static::$appPath = $appPath;
    }

    /**
     * @param TcpConnection|mixed $connection
     * @param Request|mixed $request
     * @return null
     * @throws Throwable
     */
    public function onMessage(mixed $connection, mixed $request): null
    {
        try {
            Context::set(TcpConnection::class, $connection);
            Context::set(Request::class, $request);
            $path = $request->path();
            $key = $request->method() . $path;
            if (isset(static::$callbacks[$key])) {
                [$callback, $request->plugin, $request->app, $request->controller, $request->action, $request->route] = static::$callbacks[$key];
                static::send($connection, $callback($request), $request);
                return null;
            }

            if (
                static::unsafeUri($connection, $path, $request) ||
                static::findFile($connection, $path, $key, $request) ||
                static::findRoute($connection, $path, $key, $request)
            ) {
                return null;
            }

            $controllerAndAction = static::parseControllerAction($path);
            $plugin = $controllerAndAction['plugin'] ?? static::getPluginByPath($path);
            if (!$controllerAndAction || Route::hasDisableDefaultRoute($plugin)) {
                $request->plugin = $plugin;
                $callback = static::getFallback($plugin);
                $request->app = $request->controller = $request->action = '';
                static::send($connection, $callback($request), $request);
                return null;
            }
            $app = $controllerAndAction['app'];
            $controller = $controllerAndAction['controller'];
            $action = $controllerAndAction['action'];
            $callback = static::getCallback($plugin, $app, [$controller, $action]);
            static::collectCallbacks($key, [$callback, $plugin, $app, $controller, $action, null]);
            [$callback, $request->plugin, $request->app, $request->controller, $request->action, $request->route] = static::$callbacks[$key];
            static::send($connection, $callback($request), $request);
        } catch (Throwable $e) {
            static::send($connection, static::exceptionResponse($e, $request), $request);
        }
        return null;
    }

    /**
     * @param $server
     * @return void
     */
    public function onServerStart($server): void
    {
        static::$server = $server;
        Http::requestClass(static::$requestClass);
    }

    /**
     * @param string $key
     * @param array $data
     * @return void
     */
    protected static function collectCallbacks(string $key, array $data): void
    {
        static::$callbacks[$key] = $data;
        if (count(static::$callbacks) >= 1024) {
            unset(static::$callbacks[key(static::$callbacks)]);
        }
    }

    /**
     * @param TcpConnection $connection
     * @param string $path
     * @param $request
     * @return bool
     * @throws Throwable
     */
    protected static function unsafeUri(TcpConnection $connection, string $path, $request): bool
    {
        if (
            !$path ||
            str_contains($path, '..') ||
            str_contains($path, "\\") ||
            str_contains($path, "\0")
        ) {
            $callback = static::getFallback();
            $request->plugin = $request->app = $request->controller = $request->action = '';
            static::send($connection, $callback($request), $request);
            return true;
        }
        return false;
    }

    /**
     * @param string $plugin
     * @return Closure
     */
    protected static function getFallback(string $plugin = ''): Closure
    {
        // when route, controller and action not found, try to use Route::fallback
        return Route::getFallback($plugin) ?: function () {
            return not_found();
        };
    }

    /**
     * @param Throwable $e
     * @param $request
     * @return Response
     */
    protected static function exceptionResponse(Throwable $e, $request): Response
    {
        try {
            $app = $request->app ?: '';
            $plugin = $request->plugin ?: '';
            $exceptionConfig = static::config($plugin, 'exception');
            $defaultException = $exceptionConfig[''] ?? ExceptionHandler::class;
            $exceptionHandlerClass = $exceptionConfig[$app] ?? $defaultException;

            /** @var ExceptionHandlerInterface $exceptionHandler */
            $exceptionHandler = static::container($plugin)->make($exceptionHandlerClass, [
                'logger' => static::$logger,
                'debug' => static::config($plugin, 'app.debug')
            ]);
            $exceptionHandler->report($e);
            $response = $exceptionHandler->render($request, $e);
            $response->exception($e);
            return $response;
        } catch (Throwable $e) {
            $response = new Response(500, [], static::config($plugin ?? '', 'app.debug') ? (string)$e : $e->getMessage());
            $response->exception($e);
            return $response;
        }
    }

    /**
     * @param string $plugin
     * @param string $app
     * @param $call
     * @param array|null $args
     * @param bool $withGlobalMiddleware
     * @param RouteObject|null $route
     * @return callable|Closure
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    protected static function getCallback(string $plugin, string $app, $call, array $args = null, bool $withGlobalMiddleware = true, RouteObject $route = null): callable|Closure
    {
        $args = $args === null ? null : array_values($args);
        $middlewares = [];
        if ($route) {
            $routeMiddlewares = array_reverse($route->getMiddleware());
            foreach ($routeMiddlewares as $className) {
                $middlewares[] = [$className, 'process'];
            }
        }
        $middlewares = array_merge($middlewares, Middleware::getMiddleware($plugin, $app, $withGlobalMiddleware));

        foreach ($middlewares as $key => $item) {
            $middleware = $item[0];
            if (is_string($middleware)) {
                $middleware = static::container($plugin)->get($middleware);
            } elseif ($middleware instanceof Closure) {
                $middleware = call_user_func($middleware, static::container($plugin));
            }
            if (!$middleware instanceof MiddlewareInterface) {
                throw new InvalidArgumentException('Not support middleware type');
            }
            $middlewares[$key][0] = $middleware;
        }

        $needInject = static::isNeedInject($call, $args);
        if (is_array($call) && is_string($call[0])) {
            $controllerReuse = static::config($plugin, 'app.controller_reuse', true);
            if (!$controllerReuse) {
                if ($needInject) {
                    $call = function ($request, ...$args) use ($call, $plugin) {
                        $call[0] = static::container($plugin)->make($call[0]);
                        $reflector = static::getReflector($call);
                        $args = static::resolveMethodDependencies($plugin, $request, $args, $reflector);
                        return $call(...$args);
                    };
                    $needInject = false;
                } else {
                    $call = function ($request, ...$args) use ($call, $plugin) {
                        $call[0] = static::container($plugin)->make($call[0]);
                        return $call($request, ...$args);
                    };
                }
            } else {
                $call[0] = static::container($plugin)->get($call[0]);
            }
        }

        if ($needInject) {
            $call = static::resolveInject($plugin, $call);
        }

        if ($middlewares) {
            $callback = array_reduce($middlewares, function ($carry, $pipe) {
                return function ($request) use ($carry, $pipe) {
                    try {
                        return $pipe($request, $carry);
                    } catch (Throwable $e) {
                        return static::exceptionResponse($e, $request);
                    }
                };
            }, function ($request) use ($call, $args) {
                try {
                    if ($args === null) {
                        $response = $call($request);
                    } else {
                        $response = $call($request, ...$args);
                    }
                } catch (Throwable $e) {
                    return static::exceptionResponse($e, $request);
                }
                if (!$response instanceof Response) {
                    if (!is_string($response)) {
                        $response = static::stringify($response);
                    }
                    $response = new Response(200, [], $response);
                }
                return $response;
            });
        } else {
            if ($args === null) {
                $callback = $call;
            } else {
                $callback = function ($request) use ($call, $args) {
                    return $call($request, ...$args);
                };
            }
        }
        return $callback;
    }

    /**
     * @param string $plugin
     * @param array|Closure $call
     * @return Closure
     * @see Dependency injection through reflection information
     */
    protected static function resolveInject(string $plugin, array|Closure $call): Closure
    {
        return function (Request $request, ...$args) use ($plugin, $call) {
            $reflector = static::getReflector($call);
            $args = static::resolveMethodDependencies($plugin, $request, $args, $reflector);
            return $call(...$args);
        };
    }

    /**
     * Check whether inject is required
     * @param $call
     * @param $args
     * @return bool
     * @throws ReflectionException
     */
    protected static function isNeedInject($call, $args): bool
    {
        if (is_array($call) && !method_exists($call[0], $call[1])) {
            return false;
        }
        $args = $args ?: [];
        $reflector = static::getReflector($call);
        $reflectionParameters = $reflector->getParameters();
        if (!$reflectionParameters) {
            return false;
        }
        $firstParameter = current($reflectionParameters);
        unset($reflectionParameters[key($reflectionParameters)]);
        $adaptersList = ['int', 'string', 'bool', 'array', 'object', 'float', 'mixed', 'resource'];
        foreach ($reflectionParameters as $parameter) {
            if ($parameter->hasType() && !in_array($parameter->getType()->getName(), $adaptersList)) {
                return true;
            }
        }
        if (!$firstParameter->hasType()) {
            return count($args) > count($reflectionParameters);
        }
        if (!is_a(static::$requestClass, $firstParameter->getType()->getName())) {
            return true;
        }

        return false;
    }

    /**
     * Get reflector.
     *
     * @param $call
     * @return ReflectionFunction|ReflectionMethod
     * @throws ReflectionException
     */
    protected static function getReflector($call): ReflectionMethod|ReflectionFunction
    {
        if ($call instanceof Closure || is_string($call)) {
            return new ReflectionFunction($call);
        }
        return new ReflectionMethod($call[0], $call[1]);
    }

    /**
     * Return dependent parameters
     *
     * @param string $plugin
     * @param Request $request
     * @param array $args
     * @param ReflectionFunctionAbstract $reflector
     * @return array
     */
    protected static function resolveMethodDependencies(string $plugin, Request $request, array $args, ReflectionFunctionAbstract $reflector): array
    {
        // Specification parameter information
        $args = array_values($args);
        $parameters = [];
        // An array of reflection classes for loop parameters, with each $parameter representing a reflection object of parameters
        foreach ($reflector->getParameters() as $parameter) {
            // Parameter quota consumption
            if ($parameter->hasType()) {
                $name = $parameter->getType()->getName();
                switch ($name) {
                    case 'int':
                    case 'string':
                    case 'bool':
                    case 'array':
                    case 'object':
                    case 'float':
                    case 'mixed':
                    case 'resource':
                        goto _else;
                    default:
                        if (is_a($request, $name)) {
                            //Inject Request
                            $parameters[] = $request;
                        } else {
                            $parameters[] = static::container($plugin)->make($name);
                        }
                        break;
                }
            } else {
                _else:
                // The variable parameter
                if (null !== key($args)) {
                    $parameters[] = current($args);
                } else {
                    // Indicates whether the current parameter has a default value.  If yes, return true
                    $parameters[] = $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null;
                }
                // Quota of consumption variables
                next($args);
            }
        }

        // Returns the result of parameters replacement
        return $parameters;
    }

    /**
     * @param string $plugin
     * @return ContainerInterface|array|null
     */
    public static function container(string $plugin = ''): ContainerInterface|array|null
    {
        return static::config($plugin, 'container');
    }

    /**
     * @return TcpConnection
     */
    public static function connection(): TcpConnection
    {
        return Context::get(TcpConnection::class);
    }

    /**
     * @return Request|\support\Request
     */
    public static function request(): \support\Request|Request
    {
        return Context::get(Request::class);
    }

    /**
     * @return Server|null
     */
    public static function server(): ?Server
    {
        return static::$server;
    }

    /**
     * @param TcpConnection $connection
     * @param string $path
     * @param string $key
     * @param Request|mixed $request
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     * @throws Throwable
     */
    protected static function findRoute(TcpConnection $connection, string $path, string $key, mixed $request): bool
    {
        $routeInfo = Route::dispatch($request->method(), $path);
        if ($routeInfo[0] === Dispatcher::FOUND) {
            $routeInfo[0] = 'route';
            $callback = $routeInfo[1]['callback'];
            $route = clone $routeInfo[1]['route'];
            $app = $controller = $action = '';
            $args = !empty($routeInfo[2]) ? $routeInfo[2] : null;
            if ($args) {
                $route->setParams($args);
            }
            if (is_array($callback)) {
                $controller = $callback[0];
                $plugin = static::getPluginByClass($controller);
                $app = static::getAppByController($controller);
                $action = static::getRealMethod($controller, $callback[1]) ?? '';
            } else {
                $plugin = static::getPluginByPath($path);
            }
            $callback = static::getCallback($plugin, $app, $callback, $args, true, $route);
            static::collectCallbacks($key, [$callback, $plugin, $app, $controller ?: '', $action, $route]);
            [$callback, $request->plugin, $request->app, $request->controller, $request->action, $request->route] = static::$callbacks[$key];
            static::send($connection, $callback($request), $request);
            return true;
        }
        return false;
    }

    /**
     * @param TcpConnection $connection
     * @param string $path
     * @param string $key
     * @param Request|mixed $request
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     * @throws Throwable
     */
    protected static function findFile(TcpConnection $connection, string $path, string $key, mixed $request): bool
    {
        if (preg_match('/%[0-9a-f]{2}/i', $path)) {
            $path = urldecode($path);
            if (static::unsafeUri($connection, $path, $request)) {
                return true;
            }
        }

        $pathExplodes = explode('/', trim($path, '/'));
        $plugin = '';
        if (isset($pathExplodes[1]) && $pathExplodes[0] === 'app') {
            $publicDir = BASE_PATH . "/plugin/$pathExplodes[1]/public";
            $plugin = $pathExplodes[1];
            $path = substr($path, strlen("/app/$pathExplodes[1]/"));
        } else {
            $publicDir = static::$publicPath;
        }
        $file = "$publicDir/$path";
        if (!is_file($file)) {
            return false;
        }

        if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            if (!static::config($plugin, 'app.support_php_files', false)) {
                return false;
            }
            static::collectCallbacks($key, [function () use ($file) {
                return static::execPhpFile($file);
            }, '', '', '', '', null]);
            [, $request->plugin, $request->app, $request->controller, $request->action, $request->route] = static::$callbacks[$key];
            static::send($connection, static::execPhpFile($file), $request);
            return true;
        }

        if (!static::config($plugin, 'static.enable', false)) {
            return false;
        }

        static::collectCallbacks($key, [static::getCallback($plugin, '__static__', function ($request) use ($file, $plugin) {
            clearstatcache(true, $file);
            if (!is_file($file)) {
                $callback = static::getFallback($plugin);
                return $callback($request);
            }
            return (new Response())->file($file);
        }, null, false), '', '', '', '', null]);
        [$callback, $request->plugin, $request->app, $request->controller, $request->action, $request->route] = static::$callbacks[$key];
        static::send($connection, $callback($request), $request);
        return true;
    }

    /**
     * @param TcpConnection|mixed $connection
     * @param mixed $response
     * @param Request|mixed $request
     * @return void
     * @throws Throwable
     */
    protected static function send(mixed $connection, mixed $response, mixed $request): void
    {
        if ($response === null) {
            return;
        }
        $keepAlive = $request->header('connection');
        Context::destroy();
        if (($keepAlive === null && $request->protocolVersion() === '1.1')
            || $keepAlive === 'keep-alive' || $keepAlive === 'Keep-Alive'
        ) {
            $connection->send($response);
            return;
        }
        $connection->close($response);
    }

    /**
     * @param string $path
     * @return array|false
     * @throws ReflectionException
     */
    protected static function parseControllerAction(string $path): false|array
    {
        $path = str_replace('-', '', $path);
        $pathExplode = explode('/', trim($path, '/'));
        $isPlugin = isset($pathExplode[1]) && $pathExplode[0] === 'app';
        $configPrefix = $isPlugin ? "plugin.$pathExplode[1]." : '';
        $pathPrefix = $isPlugin ? "/app/$pathExplode[1]" : '';
        $classPrefix = $isPlugin ? "plugin\\$pathExplode[1]" : '';
        $suffix = Config::get("{$configPrefix}app.controller_suffix", '');
        $relativePath = trim(substr($path, strlen($pathPrefix)), '/');
        $pathExplode = $relativePath ? explode('/', $relativePath) : [];

        $action = 'index';
        if ($controllerAction = static::guessControllerAction($pathExplode, $action, $suffix, $classPrefix)) {
            return $controllerAction;
        }
        if (count($pathExplode) <= 1) {
            return false;
        }
        $action = end($pathExplode);
        unset($pathExplode[count($pathExplode) - 1]);
        return static::guessControllerAction($pathExplode, $action, $suffix, $classPrefix);
    }

    /**
     * @param $pathExplode
     * @param $action
     * @param $suffix
     * @param $classPrefix
     * @return array|false
     * @throws ReflectionException
     */
    protected static function guessControllerAction($pathExplode, $action, $suffix, $classPrefix): false|array
    {
        $map[] = trim("$classPrefix\\app\\controller\\" . implode('\\', $pathExplode), '\\');
        foreach ($pathExplode as $index => $section) {
            $tmp = $pathExplode;
            array_splice($tmp, $index, 1, [$section, 'controller']);
            $map[] = trim("$classPrefix\\" . implode('\\', array_merge(['app'], $tmp)), '\\');
        }
        foreach ($map as $item) {
            $map[] = $item . '\\index';
        }

        foreach ($map as $controllerClass) {
            // Remove xx\xx\controller
            if (str_ends_with($controllerClass, '\\controller')) {
                continue;
            }
            $controllerClass .= $suffix;
            if ($controllerAction = static::getControllerAction($controllerClass, $action)) {
                return $controllerAction;
            }
        }
        return false;
    }

    /**
     * @param string $controllerClass
     * @param string $action
     * @return array|false
     * @throws ReflectionException
     */
    protected static function getControllerAction(string $controllerClass, string $action): false|array
    {
        // Disable calling magic methods
        if (str_starts_with($action, '__')) {
            return false;
        }

        if (($controllerClass = static::getController($controllerClass)) && ($action = static::getAction($controllerClass, $action))) {
            return [
                'plugin' => static::getPluginByClass($controllerClass),
                'app' => static::getAppByController($controllerClass),
                'controller' => $controllerClass,
                'action' => $action
            ];
        }
        return false;
    }

    /**
     * @param string $controllerClass
     * @return string|false
     * @throws ReflectionException
     */
    protected static function getController(string $controllerClass): false|string
    {
        if (class_exists($controllerClass)) {
            return (new ReflectionClass($controllerClass))->name;
        }

        $explodes = explode('\\', strtolower(ltrim($controllerClass, '\\')));
        $basePath = $explodes[0] === 'plugin' ? BASE_PATH . '/plugin' : static::$appPath;
        unset($explodes[0]);
        $fileName = array_pop($explodes) . '.php';
        $found = true;

        foreach ($explodes as $pathSection) {
            if (!$found) {
                break;
            }
            $dirs = Util::scanDir($basePath, false);
            $found = false;
            foreach ($dirs as $name) {
                $path = "$basePath/$name";
                if (is_dir($path) && strtolower($name) === $pathSection) {
                    $basePath = $path;
                    $found = true;
                    break;
                }
            }
        }

        if (!$found) {
            return false;
        }

        foreach (scandir($basePath) ?: [] as $name) {
            if (strtolower($name) === $fileName) {
                require_once "$basePath/$name";
                if (class_exists($controllerClass, false)) {
                    return (new ReflectionClass($controllerClass))->name;
                }
            }
        }
        return false;
    }

    /**
     * @param string $controllerClass
     * @param string $action
     * @return string|false
     */
    protected static function getAction(string $controllerClass, string $action): false|string
    {
        $methods = get_class_methods($controllerClass);
        $action = strtolower($action);
        $found = false;
        foreach ($methods as $candidate) {
            if (strtolower($candidate) === $action) {
                $action = $candidate;
                $found = true;
                break;
            }
        }

        if ($found) {
            return $action;
        }

        // Action is not public method
        if (method_exists($controllerClass, $action)) {
            return false;
        }

        if (method_exists($controllerClass, '__call')) {
            return $action;
        }

        return false;
    }

    /**
     * @param string $controllerClass
     * @return string
     */
    public static function getPluginByClass(string $controllerClass): string
    {
        $controllerClass = trim($controllerClass, '\\');
        $tmp = explode('\\', $controllerClass, 3);
        if ($tmp[0] !== 'plugin') {
            return '';
        }
        return $tmp[1] ?? '';
    }

    /**
     * @param string $path
     * @return string
     */
    public static function getPluginByPath(string $path): string
    {
        $path = trim($path, '/');
        $tmp = explode('/', $path, 3);
        if ($tmp[0] !== 'app') {
            return '';
        }
        return $tmp[1] ?? '';
    }

    /**
     * @param string $controllerClass
     * @return mixed|string
     */
    protected static function getAppByController(string $controllerClass): mixed
    {
        $controllerClass = trim($controllerClass, '\\');
        $tmp = explode('\\', $controllerClass, 5);
        $pos = $tmp[0] === 'plugin' ? 3 : 1;
        if (!isset($tmp[$pos])) {
            return '';
        }
        return strtolower($tmp[$pos]) === 'controller' ? '' : $tmp[$pos];
    }

    /**
     * Выполнить php файл
     * @param string $file
     * @return false|string
     */
    public static function execPhpFile(string $file): false|string
    {
        ob_start();
        // Try to include php file.
        try {
            include $file;
        } catch (Exception $e) {
            echo $e;
        }
        return ob_get_clean();
    }

    /**
     * Получить метод
     * @param string $class
     * @param string $method
     * @return string
     */
    protected static function getRealMethod(string $class, string $method): string
    {
        $method = strtolower($method);
        $methods = get_class_methods($class);
        foreach ($methods as $candidate) {
            if (strtolower($candidate) === $method) {
                return $candidate;
            }
        }
        return $method;
    }

    /**
     * Конфигурация
     * @param string $plugin
     * @param string $key
     * @param $default
     * @return array|mixed|null
     */
    protected static function config(string $plugin, string $key, $default = null): mixed
    {
        return Config::get($plugin ? "plugin.$plugin.$key" : $key, $default);
    }

    /**
     * @param mixed $data
     * @return string
     */
    protected static function stringify(mixed $data): string
    {
        $type = gettype($data);
        switch ($type) {
            case 'boolean':
                return $data ? 'true' : 'false';
            case 'NULL':
                return 'NULL';
            case 'array':
                return 'Array';
            case 'object':
                if (!method_exists($data, '__toString')) {
                    return 'Object';
                }
        }
        return (string)$data;

    }
}
