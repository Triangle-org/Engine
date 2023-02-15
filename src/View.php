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

interface View
{
    /**
     * Render.
     * @param string $template
     * @param array $vars
     * @param string|null $app
     * @return string
     */
    static function render(string $template, array $vars, string $app = null): string;
}
