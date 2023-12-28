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

namespace Triangle\Engine\Exception;

use Psr\Log\LoggerInterface;
use Throwable;
use Triangle\Engine\Http\Request;
use Triangle\Engine\Http\Response;
use function nl2br;
use function trim;

/**
 * Class Events
 */
class ExceptionHandler implements ExceptionHandlerInterface
{
    /**
     * Не сообщать об исключениях этих типов
     * @var array
     */
    public array $dontReport = [BusinessException::class];

    /**
     * @var LoggerInterface|null
     */
    protected ?LoggerInterface $logger = null;

    /**
     * @var bool
     */
    protected bool $debug = true;

    /**
     * Конструктор обработчика исключений.
     * @param LoggerInterface|null $logger
     * @param bool $debug
     */
    public function __construct(?LoggerInterface $logger = null, bool $debug = true)
    {
        $this->logger = $logger;
        $this->debug = $debug;
    }

    /**
     * Отчет об исключении
     * @param Throwable $exception
     * @return void
     */
    public function report(Throwable $exception): void
    {
        if ($this->shouldnt($exception, config('exception.dont_report') ?: $this->dontReport)) {
            return;
        }

        $logs = '';
        if ($request = request()) {
            $logs = $request->getRealIp() . ' ' . $request->method() . ' ' . trim($request->fullUrl(), '/');
        }
        $this->logger->error($logs . PHP_EOL . $exception);

        // New report (Mongo) :)
        // 
        // $this->_logger->error($exception->getMessage(), [
        //     'debug' => $this->_debug,
        //     'ip' => $request->getRealIp(),
        //     'method' => $request->method(),
        //     'post' => $request->post(),
        //     'get' => $request->get(),
        //     'url' => \trim($request->fullUrl(), '/'),
        //     'exception' => [
        //         'code' => $exception->getCode() ?? 0,
        //         'file' => $exception->getFile(),
        //         'line' => $exception->getLine(),
        //         'message' => $exception->getMessage(),
        //         'previous' => $exception->getPrevious(),
        //         'trace' => $exception->getTrace(),
        //     ]
        // ]);
    }

    /**
     * Проверка, следует ли игнорировать исключение
     * @param Throwable $e
     * @param array $exceptions
     * @return bool
     */
    protected function shouldnt(Throwable $e, array $exceptions): bool
    {
        foreach ($exceptions as $type) {
            if ($e instanceof $type) {
                return true;
            }
        }
        return false;
    }

    /**
     * Рендеринг исключения
     * @param Request $request
     * @param Throwable $exception
     * @return Response
     * @throws Throwable
     */
    public function render(Request $request, Throwable $exception): Response
    {
        if (method_exists($exception, 'render')) {
            return $exception->render($request);
        }

        $json = [
            'status' => $exception->getCode() ?: 500,
            'error' => $this->debug ? $exception->getMessage() : "Внутренняя ошибка",
        ];

        if ($this->debug) {
            $json['traces'] = nl2br((string)$exception);
        }
        // Ответ JSON
        if ($request->expectsJson()) return responseJson($json);

        return response($json, 500);
    }
}
