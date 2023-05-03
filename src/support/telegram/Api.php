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

namespace support\telegram;

use BadMethodCallException;
use Illuminate\Support\Traits\Macroable;
use support\telegram\Exceptions\TelegramSDKException;
use support\telegram\HttpClients\HttpClientInterface;

/**
 * Class Api.
 *
 * @mixin Commands\CommandBus
 */
class Api
{
    use Macroable {
        __call as macroCall;
    }

    use Events\EmitsEvents;

    use Traits\Http;
    use Traits\CommandsHandler;
    use Traits\HasContainer;

    use Methods\Chat;
    use Methods\Commands;
    use Methods\EditMessage;
    use Methods\Game;
    use Methods\Get;
    use Methods\Location;
    use Methods\Message;
    use Methods\Passport;
    use Methods\Payments;
    use Methods\Query;
    use Methods\Stickers;
    use Methods\Update;

    /** @var string Version number of the Telegram Bot PHP SDK. */
    const VERSION = '3.0.0';

    /** @var string The name of the environment variable that contains the Telegram Bot API Access Token. */
    const BOT_TOKEN_ENV_NAME = 'TELEGRAM_BOT_TOKEN';

    /**
     * Instantiates a new Telegram super-class object.
     *
     *
     * @param string|null $token The Telegram Bot API Access Token.
     * @param bool $async (Optional) Indicates if the request to Telegram will be asynchronous (non-blocking).
     * @param HttpClientInterface|null $httpClientHandler (Optional) Custom HTTP Client Handler.
     * @param string|null $base_bot_url (Optional) Custom base bot url.
     *
     * @throws TelegramSDKException
     */
    public function __construct(?string $token = null, bool $async = false, ?HttpClientInterface $httpClientHandler = null, ?string $base_bot_url = null)
    {
        $this->accessToken = $token ?? getenv(static::BOT_TOKEN_ENV_NAME);
        $this->validateAccessToken();

        if ($async) {
            $this->setAsyncRequest($async);
        }

        $this->httpClientHandler = $httpClientHandler;

        $this->baseBotUrl = $base_bot_url;
    }

    /**
     * @param array $config
     * @return BotsManager
     * @deprecated This method will be removed in SDK v4.
     * Invoke Bots Manager.
     *
     */
    public static function manager(array $config): BotsManager
    {
        return new BotsManager($config);
    }

    /**
     * Метод для обработки любых динамических методов.
     *
     * @param $method
     * @param $arguments
     *
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $arguments);
        }

        if (method_exists($this, $method)) {
            return call_user_func_array([$this, $method], $arguments);
        }

        // Если метод не существует в API - пробуем commandBus.
        if (preg_match('/^\w+Commands?/', $method, $matches)) {
            return call_user_func_array([$this->getCommandBus(), $matches[0]], $arguments);
        }

        throw new BadMethodCallException("Метод [$method] не существует.");
    }

    /**
     * @throws TelegramSDKException
     */
    private function validateAccessToken(): void
    {
        if (!$this->accessToken || !is_string($this->accessToken)) {
            throw TelegramSDKException::tokenNotProvided(static::BOT_TOKEN_ENV_NAME);
        }
    }
}
