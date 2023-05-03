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

namespace support\telegram\Answers;

use BadMethodCallException;
use Illuminate\Support\Str;
use support\telegram\Objects\Update;
use support\telegram\Traits\Telegram;

/**
 * Class Answerable.
 *
 * @method mixed replyWithMessage($use_sendMessage_parameters)       Reply Chat with a message. You can use all the sendMessage() parameters except chat_id.
 * @method mixed replyWithPhoto($use_sendPhoto_parameters)           Reply Chat with a Photo. You can use all the sendPhoto() parameters except chat_id.
 * @method mixed replyWithAudio($use_sendAudio_parameters)           Reply Chat with an Audio message. You can use all the sendAudio() parameters except chat_id.
 * @method mixed replyWithVideo($use_sendVideo_parameters)           Reply Chat with a Video. You can use all the sendVideo() parameters except chat_id.
 * @method mixed replyWithVoice($use_sendVoice_parameters)           Reply Chat with a Voice message. You can use all the sendVoice() parameters except chat_id.
 * @method mixed replyWithDocument($use_sendDocument_parameters)     Reply Chat with a Document. You can use all the sendDocument() parameters except chat_id.
 * @method mixed replyWithSticker($use_sendSticker_parameters)       Reply Chat with a Sticker. You can use all the sendSticker() parameters except chat_id.
 * @method mixed replyWithLocation($use_sendLocation_parameters)     Reply Chat with a Location. You can use all the sendLocation() parameters except chat_id.
 * @method mixed replyWithChatAction($use_sendChatAction_parameters) Reply Chat with a Chat Action. You can use all the sendChatAction() parameters except chat_id.
 */
trait Answerable
{
    use Telegram;

    /**
     * @var Update Holds an Update object.
     */
    protected Update $update;

    /**
     * Magic Method to handle all ReplyWith Methods.
     *
     * @return mixed|string
     */
    public function __call(string $method, array $parameters)
    {
        if (!Str::startsWith($method, 'replyWith')) {
            throw new BadMethodCallException(sprintf('Method [%s] does not exist.', $method));
        }

        $replyName = Str::studly(substr($method, 9));
        $methodName = 'send' . $replyName;

        if (!method_exists($this->telegram, $methodName)) {
            throw new BadMethodCallException(sprintf('Method [%s] does not exist.', $method));
        }

        $chatId = $this->update->getChat()->id ?? null;
        if (!$chatId) {
            throw new BadMethodCallException(sprintf('No chat available for reply with [%s].', $method));
        }

        $params = array_merge(['chat_id' => $chatId], $parameters[0]);

        return $this->telegram->{$methodName}($params);
    }

    /**
     * Returns Update object.
     */
    public function getUpdate(): Update
    {
        return $this->update;
    }
}
