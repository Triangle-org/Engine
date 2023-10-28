<?php

use support\Request;
use support\Translation;
use Triangle\Engine\App;
use Triangle\Engine\Config;
use Triangle\Engine\Http\Request as TriangleRequest;
use Triangle\Engine\Route;

/**
 * Copy dir
 * @param string $source
 * @param string $dest
 * @param bool $overwrite
 * @return void
 */
function copy_dir(string $source, string $dest, bool $overwrite = false): void
{
    if (is_dir($source)) {
        if (!is_dir($dest)) {
            mkdir($dest);
        }
        $files = scandir($source);
        foreach ($files as $file) {
            if ($file !== "." && $file !== "..") {
                copy_dir("$source/$file", "$dest/$file");
            }
        }
    } else if (file_exists($source) && ($overwrite || !file_exists($dest))) {
        copy($source, $dest);
    }
}

/**
 * ScanDir.
 * @param string $basePath
 * @param bool $withBasePath
 * @return array
 */
function scan_dir(string $basePath, bool $withBasePath = true): array
{
    if (!is_dir($basePath)) {
        return [];
    }
    $paths = array_diff(scandir($basePath), array('.', '..')) ?: [];
    return $withBasePath ? array_map(static function ($path) use ($basePath) {
        return $basePath . DIRECTORY_SEPARATOR . $path;
    }, $paths) : $paths;
}

/**
 * Remove dir
 * @param string $dir
 * @return bool
 */
function remove_dir(string $dir): bool
{
    if (is_link($dir) || is_file($dir)) {
        return unlink($dir);
    }
    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
        (is_dir("$dir/$file") && !is_link($dir)) ? remove_dir("$dir/$file") : unlink("$dir/$file");
    }
    return rmdir($dir);
}