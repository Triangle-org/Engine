<?php

/**
 * @package     Triangle Engine
 * @link        https://github.com/Triangle-org/Engine
 * 
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright (c) 2018-2023 Localzet Group
 * @license     https://www.localzet.com/license GNU GPLv3 License
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
     * @param  string  $token             The Telegram Bot API Access Token.
     * @param  bool  $async             (Optional) Indicates if the request to Telegram will be asynchronous (non-blocking).
     * @param  HttpClientInterface|null  $httpClientHandler (Optional) Custom HTTP Client Handler.
     * @param  string|null  $base_bot_url (Optional) Custom base bot url.
     *
     * @throws TelegramSDKException
     */
    public function __construct($token = null, $async = false, $httpClientHandler = null, $base_bot_url = null)
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
     * @deprecated This method will be removed in SDK v4.
     * Invoke Bots Manager.
     *
     * @param  array  $config
     * @return BotsManager
     */
    public static function manager($config): BotsManager
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
    private function validateAccessToken()
    {
        if (!$this->accessToken || !is_string($this->accessToken)) {
            throw TelegramSDKException::tokenNotProvided(static::BOT_TOKEN_ENV_NAME);
        }
    }
}
