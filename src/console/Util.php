<?php

/**
 * @package     Triangle Engine (FrameX Project)
 * @link        https://github.com/localzet/FrameX      FrameX Project v1-2
 * @link        https://github.com/Triangle-org/Engine  Triangle Engine v2+
 *
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright (c) 2018-2023 Localzet Group
 * @license     https://www.gnu.org/licenses/agpl AGPL-3.0 license
 *
 *              This program is free software: you can redistribute it and/or modify
 *              it under the terms of the GNU Affero General Public License as
 *              published by the Free Software Foundation, either version 3 of the
 *              License, or (at your option) any later version.
 *
 *              This program is distributed in the hope that it will be useful,
 *              but WITHOUT ANY WARRANTY; without even the implied warranty of
 *              MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *              GNU Affero General Public License for more details.
 *
 *              You should have received a copy of the GNU Affero General Public License
 *              along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Triangle\Engine\Console;

class Util
{
    public static function nameToNamespace($name)
    {
        $namespace = ucfirst($name);
        $namespace = preg_replace_callback(['/-([a-zA-Z])/', '/(\/[a-zA-Z])/'], function ($matches) {
            return strtoupper($matches[1]);
        }, $namespace);
        return str_replace('/', '\\', ucfirst($namespace));
    }

    public static function classToName($class)
    {
        $class = lcfirst($class);
        return preg_replace_callback(['/([A-Z])/'], function ($matches) {
            return '_' . strtolower($matches[1]);
        }, $class);
    }

    public static function nameToClass($class)
    {
        $class = preg_replace_callback(['/-([a-zA-Z])/', '/_([a-zA-Z])/'], function ($matches) {
            return strtoupper($matches[1]);
        }, $class);

        if (!($pos = strrpos($class, '/'))) {
            $class = ucfirst($class);
        } else {
            $path = substr($class, 0, $pos);
            $class = ucfirst(substr($class, $pos + 1));
            $class = "$path/$class";
        }
        return $class;
    }
}
