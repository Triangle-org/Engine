<?php declare(strict_types=1);

/**
 * @package     Triangle Engine (FrameX Project)
 * @link        https://github.com/Triangle-org/Engine Triangle Engine (v2+)
 * @link        https://github.com/localzet-archive/FrameX-Public FrameX (v1-2)
 *
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright (c) 2023-2025 Triangle Framework Team
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

namespace Triangle\Exception;

use Psr\Log\LoggerInterface;
use Throwable;
use Triangle\Engine\Request;
use Triangle\Engine\Response;
use function nl2br;

/**
 * Class Events
 */
class ExceptionHandler implements ExceptionHandlerInterface
{
    /**
     * Не сообщать об исключениях этих типов
     */
    public array $dontReport = [BusinessException::class];

    /**
     * Конструктор обработчика исключений.
     */
    public function __construct(protected ?LoggerInterface $logger = null, protected bool $debug = false)
    {
    }

    /**
     * Отчет об исключении
     */
    public function report(Throwable $throwable): void
    {
        if ($this->shouldnt($throwable, config('exception.dont_report') ?: $this->dontReport)) {
            return;
        }

        $this->logger->error($throwable->getMessage());
    }

    /**
     * Проверка, следует ли игнорировать исключение
     */
    protected function shouldnt(Throwable $throwable, array $exceptions): bool
    {
        foreach ($exceptions as $exception) {
            if ($throwable instanceof $exception) {
                return true;
            }
        }

        return false;
    }

    /**
     * Рендеринг исключения
     * @throws Throwable
     */
    public function render(Request $request, Throwable $throwable): Response
    {
        if (method_exists($throwable, 'render')) {
            return $throwable->render($request, $this->debug);
        }

        $json = [
            'status' => $throwable->getCode() ?: 500,
            'error' => $this->debug ? $throwable->getMessage() : "Внутренняя ошибка",
        ];

        if ($this->debug) {
            $json['debug'] = $this->debug;
            $json['traces'] = nl2br((string)$throwable);
        }

        $status = config('app.http_always_200') ? 200 : $json['status'];

        if (!function_exists('responseView') || request()->expectsJson()) {
            return responseJson($json, $status);
        }

        return responseView($json, $status);
    }
}
