<?php

use localzet\Server;
use support\Container;

/**
 * @param $server
 * @param $class
 */
function server_bind($server, $class): void
{
    $callbackMap = [
        'onConnect',
        'onMessage',
        'onClose',
        'onError',
        'onBufferFull',
        'onBufferDrain',
        'onServerStop',
        'onWebSocketConnect',
        'onServerReload'
    ];
    foreach ($callbackMap as $name) {
        if (method_exists($class, $name)) {
            $server->$name = [$class, $name];
        }
    }
    if (method_exists($class, 'onServerStart')) {
        call_user_func([$class, 'onServerStart'], $server);
    }
}

/**
 * @param $processName
 * @param $config
 * @return void
 */
function server_start($processName, $config): void
{
    $server = new Server($config['listen'] ?? null, $config['context'] ?? []);
    $propertyMap = [
        'count',
        'user',
        'group',
        'reloadable',
        'reusePort',
        'transport',
        'protocol',
    ];
    $server->name = $processName;
    foreach ($propertyMap as $property) {
        if (isset($config[$property])) {
            $server->$property = $config[$property];
        }
    }

    $server->onServerStart = function ($server) use ($config) {
        require_once base_path('/support/bootstrap.php');

        foreach ($config['services'] ?? [] as $service) {
            if (!class_exists($service['handler'])) {
                echo "process error: class {$service['handler']} not exists\r\n";
                continue;
            }
            $listen = new Server($service['listen'] ?? null, $service['context'] ?? []);
            if (isset($service['listen'])) {
                echo "listen: {$service['listen']}\n";
            }
            $instance = Container::make($service['handler'], $service['constructor'] ?? []);
            server_bind($listen, $instance);
            $listen->listen();
        }

        if (isset($config['handler'])) {
            if (!class_exists($config['handler'])) {
                echo "process error: class {$config['handler']} not exists\r\n";
                return;
            }

            $instance = Container::make($config['handler'], $config['constructor'] ?? []);
            server_bind($server, $instance);
        }
    };
}

/**
 * @return int
 */
function cpu_count(): int
{
    if (DIRECTORY_SEPARATOR === '\\') {
        return 1;
    }
    $count = 4;
    if (is_callable('shell_exec')) {
        if (strtolower(PHP_OS) === 'darwin') {
            $count = (int)shell_exec('sysctl -n machdep.cpu.core_count');
        } else {
            $count = (int)shell_exec('nproc');
        }
    }
    return $count > 0 ? $count : 4;
}