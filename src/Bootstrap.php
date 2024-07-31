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

use localzet\Server;
use support\Log;
use Triangle\Engine\Interface\BootstrapInterface;

class Bootstrap
{
    public static function start(?Server $server = null): void
    {
        $bootstrap = config('bootstrap', []);
        self::load($bootstrap, $server);

        $plugins = config('plugin', []);
        foreach ($plugins as $firm => $projects) {
            foreach ($projects as $name => $project) {
                if (is_array($project) && !empty($project['bootstrap'])) {
                    self::load($project['bootstrap'], $server);
                }
            }

            if (!empty($project['bootstrap'])) {
                self::load($projects['bootstrap'], $server);
            }
        }
    }

    public static function load(array $classes, ?Server $server = null): void
    {
        foreach ($classes as $class) {
            if (!class_exists($class)) {
                self::log("Внимание! Класса $class не существует\n");
                continue;
            }

            /** @var BootstrapInterface $class */
            $class::start($server);
        }
    }

    protected static function log($text): void
    {
        echo $text;
        Log::error($text);
    }
}