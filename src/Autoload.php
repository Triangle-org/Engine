<?php
/**
 * @package     Triangle Engine (FrameX Project)
 * @link        https://github.com/Triangle-org/Engine Triangle Engine (v2+)
 * @link        https://github.com/localzet-archive/FrameX-Public FrameX (v1-2)
 *
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright (c) 2023-2024 Triangle Framework Team
 * @license     https://www.gnu.org/licenses/agpl-3.0 GNU Affero General Public License v3.0
 *
 *              This program is free software: you can redistribute it and/or modify
 *              it under the terms of the GNU Affero General Public License as published
 *              by the Free Software Foundation, either version 3 of the License, or
 *              (at your option) any later version.
 *
 *              This program is distributed in the hope that it will be useful,
 *              but WITHOUT ANY WARRANTY; without even the implied warranty of
 *              MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *              GNU Affero General Public License for more details.
 *
 *              You should have received a copy of the GNU Affero General Public License
 *              along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 *              For any questions, please contact <support@localzet.com>
 */

namespace Triangle\Engine;

class Autoload
{
    private const LOADERS = [
        [\Triangle\Engine\Autoload\FileLoader::class, 'loadAll'],
        [\Triangle\Engine\Autoload\BootstrapLoader::class, 'loadAll'],
        [\Triangle\Engine\Autoload\EventLoader::class, 'loadAll'],
        [\Triangle\Engine\Autoload\MiddlewareLoader::class, 'loadAll'],
    ];

    public static function loadAll(array $addLoaders = []): void
    {
        foreach (self::LOADERS + $addLoaders as $loader) {
            if (class_exists($loader[0]) && method_exists($loader[0], $loader[1])) {
                $loader[0]::$loader[1]();
            }
        }
    }
}