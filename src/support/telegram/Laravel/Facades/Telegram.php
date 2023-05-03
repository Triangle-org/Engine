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

namespace support\telegram\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use support\telegram\BotsManager;

/**
 * @see \support\telegram\BotsManager
 *
 * @method static \support\telegram\BotsManager setContainer(\Psr\Container\ContainerInterface $container)
 * @method static array getBotConfig(?string $name = null)
 * @method static \support\telegram\Api bot(?string $name = null)
 * @method static \support\telegram\Api reconnect(?string $name = null)
 * @method static \support\telegram\BotsManager disconnect(?string $name = null)
 * @method static bool hasBot(string $name)
 * @method static mixed getConfig(string $key, mixed $default = null)
 * @method static null|string getDefaultBotName()
 * @method static \support\telegram\BotsManager setDefaultBot(string $name)
 * @method static array getBots()
 * @method static array parseBotCommands(array $commands)
 *
 * @see \support\telegram\Api
 *
 * @method static \support\telegram\BotsManager manager(array $config)
 * @method static void macro($name, $macro)
 * @method static void mixin($mixin, $replace = true)
 * @method static void hasMacro($name)
 * @method static void flushMacros()
 * @method static void macroCall($method, $parameters)
 * @method static void useEventDispatcher(\support\telegram\Events\EventDispatcherListenerContract $emitter)
 * @method static \support\telegram\Events\EventDispatcherListenerContract eventDispatcher()
 * @method static bool hasEventDispatcher()
 * @method static void on(string $event, callable $listener, int $priority = 0)
 * @method static \support\telegram\Api setAsyncRequest(bool $isAsyncRequest)
 * @method static \support\telegram\Api setHttpClientHandler(\support\telegram\HttpClients\HttpClientInterface $httpClientHandler)
 * @method static \support\telegram\Api setBaseBotUrl(string $baseBotUrl)
 * @method static null|\support\telegram\TelegramResponse getLastResponse()
 * @method static string downloadFile(\support\telegram\Objects\File|\support\telegram\Objects\BaseObject|string $file, string $filename)
 * @method static string getAccessToken()
 * @method static \support\telegram\Api setAccessToken(string $accessToken)
 * @method static bool isAsyncRequest()
 * @method static int getTimeOut()
 * @method static \support\telegram\Api setTimeOut(int $timeOut)
 * @method static int getConnectTimeOut()
 * @method static \support\telegram\Api setConnectTimeOut(int $connectTimeOut)
 * @method static \support\telegram\TelegramClient getClient()
 * @method static \support\telegram\Commands\CommandBus getCommandBus()
 * @method static \support\telegram\Api setCommandBus(\support\telegram\Commands\CommandBus $commandBus)
 * @method static \support\telegram\Objects\Update|array commandsHandler(bool $webhook = false, ?\Psr\Http\Message\RequestInterface $request = null)
 * @method static void processCommand(\support\telegram\Objects\Update $update)
 * @method static mixed triggerCommand(string $name, \support\telegram\Objects\Update $update, ?array $entity = null)
 * @method static \Psr\Container\ContainerInterface getContainer()
 * @method static bool hasContainer()
 * @method static bool kickChatMember(array $params)
 * @method static bool banChatMember(array $params)
 * @method static string exportChatInviteLink(array $params)
 * @method static \support\telegram\Objects\ChatInviteLink createChatInviteLink(array $params)
 * @method static \support\telegram\Objects\ChatInviteLink editChatInviteLink(array $params)
 * @method static \support\telegram\Objects\ChatInviteLink revokeChatInviteLink(array $params)
 * @method static bool approveChatJoinRequest(array $params)
 * @method static bool declineChatJoinRequest(array $params)
 * @method static bool setChatPhoto(array $params)
 * @method static bool deleteChatPhoto(array $params)
 * @method static bool setChatTitle(array $params)
 * @method static bool setChatDescription(array $params)
 * @method static bool pinChatMessage(array $params)
 * @method static bool unpinChatMessage(array $params)
 * @method static bool unpinAllChatMessages(array $params)
 * @method static bool leaveChat(array $params)
 * @method static bool unbanChatMember(array $params)
 * @method static bool restrictChatMember(array $params)
 * @method static bool promoteChatMember(array $params)
 * @method static bool setChatAdministratorCustomTitle(array $params)
 * @method static bool banChatSenderChat(array $params)
 * @method static bool unbanChatSenderChat(array $params)
 * @method static bool setChatPermissions(array $params)
 * @method static \support\telegram\Objects\Chat getChat(array $params)
 * @method static array getChatAdministrators(array $params)
 * @method static int getChatMemberCount(array $params)
 * @method static \support\telegram\Objects\ChatMember getChatMember(array $params)
 * @method static bool setChatStickerSet(array $params)
 * @method static bool deleteChatStickerSet(array $params)
 * @method static bool setMyCommands(array $params)
 * @method static bool deleteMyCommands(array $params = [])
 * @method static array getMyCommands(array $params = [])
 * @method static \support\telegram\Objects\Message editMessageText(array $params)
 * @method static \support\telegram\Objects\Message editMessageCaption(array $params)
 * @method static \support\telegram\Objects\Message editMessageMedia(array $params)
 * @method static \support\telegram\Objects\Message editMessageReplyMarkup(array $params)
 * @method static \support\telegram\Objects\Poll stopPoll(array $params)
 * @method static void deleteMessage(array $params)
 * @method static \support\telegram\Objects\Message sendGame(array $params)
 * @method static \support\telegram\Objects\Message setGameScore(array $params)
 * @method static array getGameHighScores(array $params)
 * @method static \support\telegram\Objects\User getMe()
 * @method static \support\telegram\Objects\UserProfilePhotos getUserProfilePhotos(array $params)
 * @method static \support\telegram\Objects\File getFile(array $params)
 * @method static \support\telegram\Objects\Message sendLocation(array $params)
 * @method static \support\telegram\Objects\Message editMessageLiveLocation(array $params)
 * @method static \support\telegram\Objects\Message stopMessageLiveLocation(array $params)
 * @method static \support\telegram\Objects\Message sendMessage(array $params)
 * @method static \support\telegram\Objects\Message forwardMessage(array $params)
 * @method static \support\telegram\Objects\Message copyMessage(array $params)
 * @method static \support\telegram\Objects\Message sendPhoto(array $params)
 * @method static \support\telegram\Objects\Message sendAudio(array $params)
 * @method static \support\telegram\Objects\Message sendDocument(array $params)
 * @method static \support\telegram\Objects\Message sendVideo(array $params)
 * @method static \support\telegram\Objects\Message sendAnimation(array $params)
 * @method static \support\telegram\Objects\Message sendVoice(array $params)
 * @method static \support\telegram\Objects\Message sendVideoNote(array $params)
 * @method static \support\telegram\Objects\Message sendMediaGroup(array $params)
 * @method static \support\telegram\Objects\Message sendVenue(array $params)
 * @method static \support\telegram\Objects\Message sendContact(array $params)
 * @method static \support\telegram\Objects\Message sendPoll(array $params)
 * @method static \support\telegram\Objects\Message sendDice(array $params)
 * @method static bool sendChatAction(array $params)
 * @method static void setPassportDataErrors(array $params)
 * @method static \support\telegram\Objects\Message sendInvoice(array $params)
 * @method static bool answerShippingQuery(array $params)
 * @method static bool answerPreCheckoutQuery(array $params)
 * @method static bool answerCallbackQuery(array $params)
 * @method static bool answerInlineQuery(array $params)
 * @method static \support\telegram\Objects\Message sendSticker(array $params)
 * @method static \support\telegram\Objects\StickerSet getStickerSet(array $params)
 * @method static \support\telegram\Objects\File uploadStickerFile(array $params)
 * @method static bool createNewStickerSet(array $params)
 * @method static bool addStickerToSet(array $params)
 * @method static bool setStickerPositionInSet(array $params)
 * @method static bool deleteStickerFromSet(array $params)
 * @method static bool setStickerSetThumb(array $params)
 * @method static array getUpdates(array $params = [], bool $shouldDispatchEvents = true)
 * @method static bool setWebhook(array $params)
 * @method static bool deleteWebhook()
 * @method static \support\telegram\Objects\WebhookInfo getWebhookInfo()
 * @method static \support\telegram\Objects\Update getWebhookUpdate(bool $shouldDispatchEvents = true, ?\Psr\Http\Message\RequestInterface $request = null)
 * @method static bool removeWebhook()
 *
 * @see \support\telegram\Commands\CommandBus
 *
 * @method static array getCommands()
 * @method static \support\telegram\Commands\CommandBus addCommands(array $commands)
 * @method static \support\telegram\Commands\CommandBus addCommand(\support\telegram\Commands\CommandInterface|string $command)
 * @method static \support\telegram\Commands\CommandBus removeCommand(string $name)
 * @method static \support\telegram\Commands\CommandBus removeCommands(array $names)
 */
final class Telegram extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return BotsManager::class;
    }
}
