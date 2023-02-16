<?php

/**
 * @package     Triangle Engine
 * @link        https://github.com/Triangle-org/Engine
 * 
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright (c) 2018-2023 Localzet Group
 * @license     https://www.localzet.com/license GNU GPLv3 License
 */

namespace support\telegram\Traits;

use Illuminate\Contracts\Container\Container;

/**
 * HasContainer.
 */
trait HasContainer
{
    /**
     * @var Container IoC Container
     */
    protected static $container = null;

    /**
     * Set the IoC Container.
     *
     * @param $container Container instance
     */
    public static function setContainer(Container $container)
    {
        self::$container = $container;
    }

    /**
     * Get the IoC Container.
     *
     * @return Container
     */
    public function getContainer(): Container
    {
        return self::$container;
    }

    /**
     * Check if IoC Container has been set.
     *
     * @return bool
     */
    public function hasContainer(): bool
    {
        return self::$container !== null;
    }
}
