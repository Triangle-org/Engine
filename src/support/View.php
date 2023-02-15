<?php

/**
 * @package     Triangle Engine
 * @link        https://github.com/Triangle-org/Engine
 * 
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright (c) 2018-2023 Localzet Group
 * @license     https://www.localzet.com/license GNU GPLv3 License
 */

namespace support;

use function config;
use function request;

class View
{
    /**
     * @param mixed $name
     * @param mixed $value
     * @return void
     */
    public static function assign($name, $value = null)
    {
        $request = request();
        $plugin = $request->plugin ?? '';
        $handler = config($plugin ? "plugin.$plugin.view.handler" : 'view.handler');
        $handler::assign($name, $value);
    }

    public static function vars()
    {
        $request = \request();
        $plugin =  $request->plugin ?? '';
        $handler = \config($plugin ? "plugin.$plugin.view.handler" : 'view.handler');
        return $handler::vars();
    }
}
