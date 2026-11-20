<?php declare(strict_types=1);

/**
 * @package Triangle Engine
 * @link https://github.com/Triangle-org/Engine
 * @author Ivan Zorin <creator@localzet.com>
 * @copyright Copyright © 2016-2026 Localzet Group. All rights reserved.
 * @license https://www.gnu.org/licenses/agpl-3.0 GNU Affero General Public License v3.0
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

use localzet\Server;
use localzet\Server\Connection\TcpConnection;
use localzet\Server\Protocols\Http;
use Triangle\Engine\Context;
use Triangle\Engine\Request;
use Triangle\Engine\Response;

$port = (int)($argv[2] ?? 0);
$runtimeDirectory = (string)($argv[3] ?? '');

if (!function_exists('posix_setpgid') || !posix_setpgid(0, 0) || posix_getpgrp() !== getmypid()) {
    fwrite(STDERR, "Could not isolate the HTTP integration process group.\n");
    exit(1);
}

file_put_contents($runtimeDirectory . '/process-group', (string)posix_getpgrp(), LOCK_EX);

require dirname(__DIR__, 2) . '/vendor/autoload.php';

Server::$pidFile = $runtimeDirectory . '/server.pid';
Server::$statusFile = $runtimeDirectory . '/server.status';
Server::$stdoutFile = $runtimeDirectory . '/stdout.log';
Server::$logFile = $runtimeDirectory . '/server.log';

Http::requestClass(Request::class);
Context::init();

$server = new Server("http://127.0.0.1:$port");
$server->count = 1;
$server->onMessage = static function (TcpConnection $connection, Request $request): void {
    Context::set('request_id', $request->get('request_id'));

    try {
        $body = triangle_json(['request_id' => Context::get('request_id')]);
        $connection->send(new Response(200, ['Connection' => 'close'], $body));
        $connection->close();
    } finally {
        Context::destroy();
    }
};

Server::runAll();
