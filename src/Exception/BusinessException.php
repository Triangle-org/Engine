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

namespace Triangle\Engine\Exception;

use RuntimeException;
use Throwable;
use Triangle\Engine\Http\Request;
use Triangle\Engine\Http\Response;
use Triangle\Engine\Interface\ExceptionInterface;
use function nl2br;

/**
 * Класс BusinessException
 * Этот класс представляет собой пользовательское исключение, которое может быть использовано для обработки ошибок бизнес-логики.
 */
class BusinessException extends RuntimeException implements ExceptionInterface
{
    /**
     * Рендеринг исключения
     * Этот метод вызывается для отображения исключения пользователю.
     * @param Request $request Текущий HTTP-запрос
     * @return Response|null Ответ, который следует отправить пользователю
     * @throws Throwable
     */
    public function render(Request $request): ?Response
    {
        $json = [
            'status' => $this->getCode() ?? 500,
            'error' => $this->getMessage(),
        ];

        if (config('app.debug')) {
            $json['debug'] = config('app.debug');
            $json['traces'] = nl2br((string)$this);
        }

        if ($request->expectsJson()) return responseJson($json);

        return responseView($json, 500);
    }
}
