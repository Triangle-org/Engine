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

namespace support\telegram\Methods;

use support\telegram\Exceptions\TelegramSDKException;
use support\telegram\Objects\GameHighScore;
use support\telegram\Objects\Message;
use support\telegram\Traits\Http;

/**
 * Class Game.
 *
 * @mixin Http
 */
trait Game
{
    /**
     * Send a game.
     *
     * <code>
     * $params = [
     *       'chat_id'                      => '',  // int|string - Required. Unique identifier for the target chat or username of the target channel (in the format "@channelusername")
     *       'game_short_name'              => '',  // string     - Required. Short name of the game, serves as the unique identifier for the game. Set up your games via Botfather.
     *       'disable_notification'         => '',  // bool       - (Optional). Sends the message silently. iOS users will not receive a notification, Android users will receive a notification with no sound.
     *       'protect_content'              => '',  // bool       - (Optional). Protects the contents of the sent message from forwarding and saving
     *       'reply_to_message_id'          => '',  // int        - (Optional). If the message is a reply, ID of the original message
     *       'allow_sending_without_reply   => '',  // bool       - (Optional). Pass True, if the message should be sent even if the specified replied-to message is not found
     *       'reply_markup'                 => '',  // string     - (Optional). A JSON-serialized object for an inline keyboard. If empty, one ‘Play game_title’ button will be shown. If not empty, the first button must launch the game.
     * ]
     * </code>
     *
     * @link https://core.telegram.org/bots/api#sendgame
     *
     * @throws TelegramSDKException
     */
    public function sendGame(array $params): Message
    {
        $response = $this->post('sendGame', $params);

        return new Message($response->getDecodedBody());
    }

    /**
     * Set the score of the specified user in a game.
     *
     * <code>
     * $params = [
     *       'user_id'               => '',  // int    - Required. User identifier
     *       'score'                 => '',  // int    - Required. New score, must be non-negative
     *       'force'                 => '',  // bool   - (Optional). Pass True, if the high score is allowed to decrease. This can be useful when fixing mistakes or banning cheaters
     *       'disable_edit_message'  => '',  // bool   - (Optional). Pass True, if the game message should not be automatically edited to include the current scoreboard
     *       'chat_id'               => '',  // int    - (Optional). Required if inline_message_id is not specified. Unique identifier for the target chat
     *       'message_id'            => '',  // int    - (Optional). Required if inline_message_id is not specified. Identifier of the sent message
     *       'inline_message_id'     => '',  // string - (Optional). Required if chat_id and message_id are not specified. Identifier of the inline message
     * ]
     * </code>
     *
     * @link https://core.telegram.org/bots/api#setgamescore
     *
     * @throws TelegramSDKException
     */
    public function setGameScore(array $params): Message
    {
        $response = $this->post('setGameScore', $params);

        return new Message($response->getDecodedBody());
    }

    /**
     * Set the score of the specified user in a game.
     *
     * <code>
     * $params = [
     *       'user_id'            => '',  // int        - Required. Target user id
     *       'chat_id'            => '',  // int|string - (Optional). Required if inline_message_id is not specified. Unique identifier for the target chat
     *       'message_id'         => '',  // int        - (Optional). Required if inline_message_id is not specified. Identifier of the sent message
     *       'inline_message_id'  => '',  // string     - (Optional). Required if chat_id and message_id are not specified. Identifier of the inline message
     * ]
     * </code>
     *
     * @link https://core.telegram.org/bots/api#getgamehighscores
     *
     * @return GameHighScore[]
     *
     * @throws TelegramSDKException
     */
    public function getGameHighScores(array $params): array
    {
        return collect($this->get('getGameHighScores', $params)->getResult())
            ->mapInto(GameHighScore::class)
            ->all();
    }
}
