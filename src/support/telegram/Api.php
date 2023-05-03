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
use support\telegram\Commands\CommandBus;
use support\telegram\Events\HasEventDispatcher;
use support\telegram\Exceptions\TelegramSDKException;
use support\telegram\HttpClients\HttpClientInterface;
use support\telegram\Methods\Chat;
use support\telegram\Methods\Commands;
use support\telegram\Methods\EditMessage;
use support\telegram\Methods\Game;
use support\telegram\Methods\Get;
use support\telegram\Methods\Location;
use support\telegram\Methods\Message;
use support\telegram\Methods\Passport;
use support\telegram\Methods\Payments;
use support\telegram\Methods\Query;
use support\telegram\Methods\Stickers;
use support\telegram\Methods\Update;
use support\telegram\Traits\CommandsHandler;
use support\telegram\Traits\HasContainer;
use support\telegram\Traits\Http;

/**
 * Class Api.
 *
 * @mixin CommandBus
 */
class Api
{
    use Macroable {
        Macroable::__call as macroCall;
    }
    use HasEventDispatcher;
    use Http;
    use CommandsHandler;
    use HasContainer;
    use Chat;
    use Commands;
    use EditMessage;
    use Game;
    use Get;
    use Location;
    use Message;
    use Passport;
    use Payments;
    use Query;
    use Stickers;
    use Update;

    /** @var string Version number of the Telegram Bot PHP SDK. */
    public const VERSION = '3.12.0';

    /** @var string The name of the environment variable that contains the Telegram Bot API Access Token. */
    public const BOT_TOKEN_ENV_NAME = 'TELEGRAM_BOT_TOKEN';

    private CommandBus $commandBus;

    /**
     * Instantiates a new Telegram super-class object.
     *
     *
     * @param string|null $token The Telegram Bot API Access Token.
     * @param bool $async (Optional) Indicates if the request to Telegram will be asynchronous (non-blocking).
     * @param HttpClientInterface|null $httpClientHandler (Optional) Custom HTTP Client Handler.
     * @param string|null $baseBotUrl (Optional) Custom base bot url.
     *
     * @throws TelegramSDKException
     */
    public function __construct(string $token = null, bool $async = false, HttpClientInterface $httpClientHandler = null, string $baseBotUrl = null)
    {
        $this->setAccessToken($token ?? getenv(self::BOT_TOKEN_ENV_NAME));
        $this->validateAccessToken();

        if ($async) {
            $this->setAsyncRequest($async);
        }

        $this->httpClientHandler = $httpClientHandler;

        $this->baseBotUrl = $baseBotUrl;
        $this->commandBus = new CommandBus($this);
    }

    /**
     * @throws TelegramSDKException
     */
    private function validateAccessToken(): void
    {
        if (!$this->getAccessToken()) {
            throw TelegramSDKException::tokenNotProvided(self::BOT_TOKEN_ENV_NAME);
        }
    }

    /**
     * @deprecated This method will be removed in SDK v4.
     * Invoke Bots Manager.
     */
    public static function manager(array $config): BotsManager
    {
        return new BotsManager($config);
    }

    /**
     * Magic method to process any dynamic method calls.
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (self::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        if (method_exists($this, $method)) {
            return $this->$method(...$parameters);
        }

        // If the method does not exist on the API, try the commandBus.
        if (preg_match('#^\w+Commands?#', $method, $matches)) {
            return $this->getCommandBus()->{$matches[0]}(...$parameters);
        }

        throw new BadMethodCallException(sprintf('Method [%s] does not exist.', $method));
    }
}
