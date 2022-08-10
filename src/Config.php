<?php

/**
 * @package     FrameX (FX) Engine
 * @link        https://localzet.gitbook.io
 * 
 * @author      localzet <creator@localzet.ru>
 * 
 * @copyright   Copyright (c) 2018-2020 Zorin Projects 
 * @copyright   Copyright (c) 2020-2022 NONA Team
 * 
 * @license     https://www.localzet.ru/license GNU GPLv3 License
 */

namespace localzet\FrameX;

class Config
{

    /**
     * @var array
     */
    protected static $_config = [];

    /**
     * @var string
     */
    protected static $_configPath = '';

    /**
     * @var bool
     */
    protected static $_loaded = false;

    /**
     * @param $config_path
     * @param array $exclude_file
     */
    public static function load($config_path, $exclude_file = [])
    {
        static::$_configPath = $config_path;
        if (!$config_path) {
            return;
        }
        $dir_iterator = new \RecursiveDirectoryIterator($config_path, \FilesystemIterator::FOLLOW_SYMLINKS);
        $iterator = new \RecursiveIteratorIterator($dir_iterator);
        foreach ($iterator as $file) {
            /** @var \SplFileInfo $file */
            if (is_dir($file) || $file->getExtension() != 'php' || \in_array($file->getBaseName('.php'), $exclude_file)) {
                continue;
            }
            $app_config_file = $file->getPath() . '/app.php';
            if (!is_file($app_config_file)) {
                continue;
            }
            $relative_path = str_replace($config_path . DIRECTORY_SEPARATOR, '', substr($file, 0, -4));
            $explode = array_reverse(explode(DIRECTORY_SEPARATOR, $relative_path));
            if (count($explode) >= 2) {
                $app_config = include $app_config_file;
                if (empty($app_config['enable'])) {
                    continue;
                }
            }
            $config = include $file;
            foreach ($explode as $section) {
                $tmp = [];
                $tmp[$section] = $config;
                $config = $tmp;
            }
            static::$_config = array_replace_recursive(static::$_config, $config);
        }

        // Merge thinkorm config
        foreach (static::$_config['plugin'] ?? [] as $firm => $projects) {
            foreach ($projects as $name => $project) {
                foreach ($project['thinkorm']['connections'] ?? [] as $key => $connection) {
                    static::$_config['thinkorm']['connections']["plugin.$firm.$name.$key"] = $connection;
                }
            }
        }
        if (!empty(static::$_config['thinkorm']['connections'])) {
            static::$_config['thinkorm']['default'] = static::$_config['thinkorm']['default'] ?? key(static::$_config['thinkorm']['connections']);
        }

        static::$_loaded = true;
    }

    /**
     * @param null $key
     * @param null $default
     * @return array|mixed|null
     */
    public static function get($key = null, $default = null)
    {
        if ($key === null) {
            return static::$_config;
        }
        $key_array = \explode('.', $key);
        $value = static::$_config;
        $finded = true;
        foreach ($key_array as $index) {
            if (!isset($value[$index])) {
                if (static::$_loaded) {
                    return $default;
                }
                $finded = false;
                break;
            }
            $value = $value[$index];
        }
        if ($finded) {
            return $value;
        }
        return static::read($key, $default);
    }

    /**
     * @param $key
     * @param $default
     * @return array|mixed|void|null
     */
    protected static function read($key, $default = null)
    {
        $path = static::$_configPath;
        if ($path === '') {
            return $default;
        }
        $keys = $key_array = \explode('.', $key);
        foreach ($key_array as $index => $section) {
            unset($keys[$index]);
            if (is_file($file = "$path/$section.php")) {
                $config = include $file;
                return static::find($keys, $config, $default);
            }
            if (!is_dir($path = "$path/$section")) {
                return $default;
            }
        }
        return $default;
    }

    /**
     * @param $key_array
     * @param $stack
     * @param $default
     * @return array|mixed
     */
    protected static function find($key_array, $stack, $default)
    {
        if (!is_array($stack)) {
            return $default;
        }
        $value = $stack;
        foreach ($key_array as $index) {
            if (!isset($value[$index])) {
                return $default;
            }
            $value = $value[$index];
        }
        return $value;
    }


    /**
     * @param $config_path
     * @param array $exclude_file
     */
    public static function reload($config_path, $exclude_file = [])
    {
        static::$_config = [];
        static::load($config_path, $exclude_file);
    }

    /**
     * @param array $config
     */
    public static function set($config)
    {
        static::$_config = array_replace_recursive(static::$_config, $config);
    }
}
