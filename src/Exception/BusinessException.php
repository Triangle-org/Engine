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

namespace Triangle\Exception;

use RuntimeException;
use Throwable;
use Triangle\Engine\Request;
use Triangle\Engine\Response;
use function nl2br;

/**
 * Класс BusinessException
 * Этот класс представляет собой пользовательское исключение, которое может быть использовано для обработки ошибок бизнес-логики.
 */
class BusinessException extends RuntimeException implements ExceptionInterface
{
    public array $data = [];
    protected bool $debug = false;

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
            'data' => $this->data,
        ];

        if (config('app.debug')) {
            $json['debug'] = config('app.debug');
            $json['traces'] = nl2br((string)$this);
        }

        return response($json, 500);
    }

    /**
     * Set data.
     * @param array|null $data
     * @return array|$this
     */
    public function data(?array $data = null): array|static
    {
        if ($data === null) {
            return $this->data;
        }
        $this->data = $data;
        return $this;
    }

    /**
     * Set debug.
     * @param bool|null $value
     * @return $this|bool
     */
    public function debug(?bool $value = null): bool|static
    {
        if ($value === null) {
            return $this->debug;
        }
        $this->debug = $value;
        return $this;
    }

    /**
     * @param string $message
     * @param array $parameters
     * @param string|null $domain
     * @param string|null $locale
     * @return string
     */
    protected function trans(string $message, array $parameters = [], string $domain = null, string $locale = null): string
    {
        $args = [];
        foreach ($parameters as $key => $parameter) {
            $args[":$key"] = $parameter;
        }
        try {
            $message = trans($message, $args, $domain, $locale);
        } catch (Throwable) {
        }
        foreach ($parameters as $key => $value) {
            $message = str_replace(":$key", $value, $message);
        }
        return $message;
    }
}
