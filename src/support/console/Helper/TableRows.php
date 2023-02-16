<?php

/**
 * @package     Triangle Engine
 * @link        https://github.com/Triangle-org/Engine
 * 
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright (c) 2018-2023 Localzet Group
 * @license     https://www.localzet.com/license GNU GPLv3 License
 */

namespace support\console\Helper;

/**
 * @internal
 */
class TableRows implements \IteratorAggregate
{
    private $generator;

    public function __construct(\Closure $generator)
    {
        $this->generator = $generator;
    }

    public function getIterator(): \Traversable
    {
        return ($this->generator)();
    }
}
