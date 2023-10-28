<?php

use Triangle\Engine\App;

define('BASE_PATH', dirname(__DIR__, 2));

/**
 * return the program execute directory
 * @param string $path
 * @return string
 */
function run_path(string $path = ''): string
{
    static $runPath = '';
    if (!$runPath) {
        $runPath = is_phar() ? dirname(Phar::running(false)) : BASE_PATH;
    }
    return path_combine($runPath, $path);
}

/**
 * @param false|string $path
 * @return string
 */
function base_path(false|string $path = ''): string
{
    if (false === $path) {
        return run_path();
    }
    return path_combine(BASE_PATH, $path);
}

/**
 * @param string $path
 * @return string
 */
function app_path(string $path = ''): string
{
    return path_combine(App::appPath(), $path);
}

/**
 * @param string $path
 * @return string
 */
function view_path(string $path = ''): string
{
    return path_combine(app_path('view'), $path);
}

/**
 * @param string $path
 * @return string
 */
function public_path(string $path = ''): string
{
//    static $publicPath = '';
//    if (!$publicPath) {
//        $publicPath = config('app.public_path') ?: run_path('public');
//    }
    return path_combine(App::publicPath(), $path);
}

/**
 * @param string $path
 * @return string
 */
function config_path(string $path = ''): string
{
    return path_combine(BASE_PATH . DIRECTORY_SEPARATOR . 'config', $path);
}

/**
 * @param string $path
 * @return string
 */
function runtime_path(string $path = ''): string
{
    static $runtimePath = '';
    if (!$runtimePath) {
        $runtimePath = config('app.runtime_path') ?: run_path('runtime');
    }
    return path_combine($runtimePath, $path);
}

/**
 * Generate paths based on given information
 * @param string $front
 * @param string $back
 * @return string
 */
function path_combine(string $front, string $back): string
{
    return $front . ($back ? (DIRECTORY_SEPARATOR . ltrim($back, DIRECTORY_SEPARATOR)) : $back);
}

/**
 * Get realpath
 * @param string $filePath
 * @return string
 */
function get_realpath(string $filePath): string
{
    if (str_starts_with($filePath, 'phar://')) {
        return $filePath;
    } else {
        return realpath($filePath);
    }
}