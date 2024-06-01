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

namespace Triangle\Engine\Middleware;

use Triangle\Engine\Http\Request;
use Triangle\Engine\Http\Response;

/**
 * Участник обработки запроса и ответа сервера.
 *
 * Компонент промежуточного программного обеспечения HTTP участвует в обработке HTTP-сообщения:
 * воздействуя на запрос, генерируя ответ или пересылая запрос последующему
 * промежуточному программному обеспечению и, возможно, действуя на его ответ.
 *
 * @see https://www.php-fig.org/psr/psr-15 PSR-15
 * @see https://github.com/php-fig/http-server-middleware HTTP Server Middleware
 */
interface MiddlewareInterface
{
    /**
     * Обработка входящего запроса к серверу.
     *
     * Обрабатывает входящий запрос к серверу для получения ответа.
     * Если не удается создать ответ самостоятельно, он может
     * делегировать это предоставленному обработчику запросов.
     *
     */
    public function process(Request $request, callable $handler): Response;
}
