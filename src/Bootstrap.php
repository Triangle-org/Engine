<?php

/**
 * @package     Triangle Engine
 * @link        https://github.com/Triangle-org/Engine
 * 
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright (c) 2018-2023 Localzet Group
 * @license     https://www.localzet.com/license GNU GPLv3 License
 */

namespace Triangle\Engine;

use localzet\Server\Server;

interface Bootstrap
{
    /**
     * onServerStart
     *
     * @param Server|null $server
     * @return mixed
     */
    public static function start(?Server $server);
}
