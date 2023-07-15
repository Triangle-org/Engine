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

namespace support\bootstrap;

use localzet\Server\Protocols\Http;
use localzet\Server\Protocols\Http\Session as SessionBase;
use localzet\Server;
use Triangle\Engine\Bootstrap;
use function config;
use function property_exists;

/**
 * Class Session
 */
class Session implements Bootstrap
{

    /**
     * @param Server|null $server
     * @return void
     */
    public static function start(?Server $server): void
    {
        $config = config('session');
        if (property_exists(SessionBase::class, 'name')) {
            SessionBase::$name = $config['session_name'];
        } else {
            Http::sessionName($config['session_name']);
        }
        SessionBase::handlerClass($config['handler'], $config['config'][$config['type']]);
        $map = [
            'auto_update_timestamp' => 'autoUpdateTimestamp',
            'cookie_lifetime' => 'cookieLifetime',
            'gc_probability' => 'gcProbability',
            'cookie_path' => 'cookiePath',
            'http_only' => 'httpOnly',
            'same_site' => 'sameSite',
            'lifetime' => 'lifetime',
            'domain' => 'domain',
            'secure' => 'secure',
        ];
        foreach ($map as $key => $name) {
            if (isset($config[$key]) && property_exists(SessionBase::class, $name)) {
                SessionBase::${$name} = $config[$key];
            }
        }
    }
}
