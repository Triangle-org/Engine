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

namespace support\telegram\Objects;

/**
 * Class CallbackQuery.
 *
 * @link https://core.telegram.org/bots/api#callbackquery
 *
 * @property int $id                        Unique message identifier.
 * @property User $from                      Sender.
 * @property Message|null $message                   (Optional). Message with the callback button that originated the query. Note that message content and message date will not be available if the message is too old.
 * @property string|null $inlineMessageId           (Optional). Identifier of the message sent via the bot in inline mode, that originated the query.
 * @property string $chatInstance              Identifier, uniquely corresponding to the chat to which the message with the callback button was sent. Useful for high scores in games.
 * @property string|null $data                      (Optional). Data associated with the callback button. Be aware that a bad client can send arbitrary data in this field.
 * @property string|null $gameShortName             (Optional). Short name of a Game to be returned, serves as the unique identifier for the game
 */
class CallbackQuery extends BaseObject
{
    /**
     * {@inheritdoc}
     *
     * @return array{from: string, message: string}
     */
    public function relations(): array
    {
        return [
            'from' => User::class,
            'message' => Message::class,
        ];
    }

    public function objectType(): ?string
    {
        //TODO - Check if message and inline_message_id are exclusive to each other
        return $this->findType(['data', 'game_short_name']);
    }
}
