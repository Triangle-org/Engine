<?php declare(strict_types=1);

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

namespace Triangle\Engine\Session;

use localzet\Server;
use localzet\Server\Protocols\Http\Session;
use Triangle\Engine\Interface\BootstrapInterface;
use function config;
use function property_exists;

/**
 * Класс Session.
 */
class Bootstrap implements BootstrapInterface
{
    /**
     * Запускает приложение.
     *
     * @param Server|null $server
     *
     * @return void
     */
    public static function start(?Server $server): void
    {
        if (!$server) {
            return;
        }

        // Получаем конфигурацию сессии.
        $config = config('session');

        // Устанавливаем обработчик сессии.
        Session::handlerClass($config['handler'], $config['config'][$config['type']]);

        // Устанавливаем параметры сессии.
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
            'session_name' => 'name'
        ];
        foreach ($map as $key => $name) {
            if (isset($config[$key]) && property_exists(Session::class, $name)) {
                Session::${$name} = $config[$key];
            }
        }
    }
}

