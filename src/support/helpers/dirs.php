<?php

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
        $files = scandir($source, SCANDIR_SORT_NONE)[2:];
        foreach ($files as $file) {
            copy_dir("$source/$file", "$dest/$file");
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
    $paths = scandir($basePath, SCANDIR_SORT_NONE)[2:];
    return $withBasePath ? array_map(fn($path) => $basePath . DIRECTORY_SEPARATOR . $path, $paths) : $paths;
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
    $files = scandir($dir, SCANDIR_SORT_NONE)[2:];
    foreach ($files as $file) {
        (is_dir("$dir/$file") && !is_link($dir)) ? remove_dir("$dir/$file") : unlink("$dir/$file");
    }
    return rmdir($dir);
}

/**
 * Create directory
 * @param string $dir
 * @return bool
 */
function create_dir(string $dir): bool
{
    return mkdir($dir);
}

/**
 * Rename directory
 * @param string $oldName
 * @param string $newName
 * @return bool
 */
function rename_dir(string $oldName, string $newName): bool
{
    return rename($oldName, $newName);
}