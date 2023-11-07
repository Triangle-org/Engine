<?php

use localzet\Server;
use localzet\Server\Connection\TcpConnection;
use Triangle\Engine\App;
use Triangle\Engine\Config;
use Triangle\Engine\Http\Request;
use Triangle\Engine\Router;

/**
 * @param string|null $key
 * @param $default
 * @return array|mixed|null
 */
function config(string $key = null, $default = null): mixed
{
    return Config::get($key, $default);
}

/**
 * @return TcpConnection|null
 */
function connection(): ?TcpConnection
{
    return App::connection();
}

/**
 * @return \support\Request|Request|null
 */
function request(): \support\Request|Request|null
{
    return App::request();
}

/**
 * @return Server|null
 */
function server(): ?Server
{
    return App::server();
}

/**
 * @return bool
 */
function is_phar(): bool
{
    return class_exists(Phar::class, false) && Phar::running();
}

/**
 * @param string $name
 * @param ...$parameters
 * @return string
 */
function route(string $name, ...$parameters): string
{
    $route = Router::getByName($name);
    if (!$route) {
        return '';
    }

    if (!$parameters) {
        return $route->url();
    }

    if (is_array(current($parameters))) {
        $parameters = current($parameters);
    }

    return $route->url($parameters);
}

/**
 * @param mixed|null $key
 * @param mixed|null $default
 * @return mixed
 * @throws Exception
 */
function session(mixed $key = null, mixed $default = null): mixed
{
    $session = request()->session();
    if (null === $key) {
        return $session;
    }
    if (is_array($key)) {
        $session->put($key);
        return null;
    }
    if (strpos($key, '.')) {
        $keyArray = explode('.', $key);
        $value = $session->all();
        foreach ($keyArray as $index) {
            if (!isset($value[$index])) {
                return $default;
            }
            $value = $value[$index];
        }
        return $value;
    }
    return $session->get($key, $default);
}

/**
 * Получение IP-адреса
 *
 * @return string|null IP-адрес
 */
function getRequestIp(): ?string
{
    $ip = request()->header(
        'x-real-ip',
        request()->header(
            'x-forwarded-for',
            request()->header(
                'client-ip',
                request()->header(
                    'x-client-ip',
                    request()->header(
                        'remote-addr',
                        request()->header(
                            'via',
                            request()->getRealIp()
                        )
                    )
                )
            )
        )
    );
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : (request()->getRealIp() ?? null);
}