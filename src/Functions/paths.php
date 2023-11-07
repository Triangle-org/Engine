<?php

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
 * return the program execute directory
 * @param string $path
 * @return string
 */
function run_path(string $path = ''): string
{
    static $runPath = '';
    if (!$runPath) {
        $runPath = is_phar() ?
            dirname(Phar::running(false)) :
            BASE_PATH;
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