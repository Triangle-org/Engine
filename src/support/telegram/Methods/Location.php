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
use support\telegram\Objects\Message as MessageObject;
use support\telegram\Traits\Http;

/**
 * Class Location.
 *
 * @mixin Http
 */
trait Location
{
    /**
     * Send point on the map.
     *
     * <code>
     * $params = [
     *       'chat_id'                     => '',  // int|string - Required. Unique identifier for the target chat or username of the target channel (in the format "@channelusername")
     *       'latitude'                    => '',  // float      - Required. Latitude of location
     *       'longitude'                   => '',  // float      - Required. Longitude of location
     *       'horizontal_accuracy          => '',  // float      - (Optional). The radius of uncertainty for the location, measured in meters; 0-1500
     *       'live_period'                 => '',  // int        - (Optional). Period in seconds for which the location will be updated (see Live Locations, should be between 60 and 86400.
     *       'heading'                     => '',  // int        - (Optional). For live locations, a direction in which the user is moving, in degrees. Must be between 1 and 360 if specified.
     *       'proximity_alert_radius'      => '',  // int        - (Optional). For live locations, a maximum distance for proximity alerts about approaching another chat member, in meters. Must be between 1 and 100000 if specified.
     *       'disable_notification'        => '',  // bool       - (Optional). Sends the message silently. iOS users will not receive a notification, Android users will receive a notification with no sound.
     *       'protect_content'             => '',  // bool       - (Optional). Protects the contents of the sent message from forwarding and saving
     *       'reply_to_message_id'         => '',  // int        - (Optional). If the message is a reply, ID of the original message
     *       'allow_sending_without_reply' => '',  // bool       - (Optional). Pass True, if the message should be sent even if the specified replied-to message is not found
     *       'reply_markup'                => '',  // string     - (Optional). Additional interface options. A JSON-serialized object for an inline keyboard, custom reply keyboard, instructions to remove reply keyboard or to force a reply from the user.
     * ]
     * </code>
     *
     * @link https://core.telegram.org/bots/api#sendlocation
     *
     * @throws TelegramSDKException
     */
    public function sendLocation(array $params): MessageObject
    {
        $response = $this->post('sendLocation', $params);

        return new MessageObject($response->getDecodedBody());
    }

    /**
     * Edit live location messages sent by the bot or via the bot.
     *
     * <code>
     * $params = [
     *       'chat_id'                => '',  // int|string - (Optional|Required). Required if inline_message_id is not specified. Unique identifier for the target chat or username of the target channel (in the format "@channelusername")
     *       'message_id'             => '',  // int        - (Optional|Required). Required if inline_message_id is not specified. Identifier of the sent message
     *       'inline_message_id'      => '',  // string     - (Optional|Required). Required if chat_id and message_id are not specified. Identifier of the inline message
     *       'latitude'               => '',  // float      - Required. Latitude of location
     *       'longitude'              => '',  // float      - Required. Longitude of location
     *       'horizontal_accuracy     => '',  // float      - (Optional). The radius of uncertainty for the location, measured in meters; 0-1500
     *       'heading'                => '',  // int        - (Optional). For live locations, a direction in which the user is moving, in degrees. Must be between 1 and 360 if specified.
     *       'proximity_alert_radius' => '',  // int        - (Optional). For live locations, a maximum distance for proximity alerts about approaching another chat member, in meters. Must be between 1 and 100000 if specified.
     *       'reply_markup'           => '',  // string     - (Optional). A JSON-serialized object for a new inline keyboard.
     * ]
     * </code>
     *
     * @link https://core.telegram.org/bots/api#editmessagelivelocation
     *
     * @return MessageObject|bool
     *
     * @throws TelegramSDKException
     */
    public function editMessageLiveLocation(array $params): MessageObject
    {
        $response = $this->post('editMessageLiveLocation', $params);

        return new MessageObject($response->getDecodedBody());
    }

    /**
     * Stop updating a live location message sent by the bot or via the bot.
     *
     * <code>
     * $params = [
     *       'chat_id'            => '',  // int|string - (Optional|Required). Required if inline_message_id is not specified. Unique identifier for the target chat or username of the target channel (in the format "@channelusername")
     *       'message_id'         => '',  // int        - (Optional|Required). Required if inline_message_id is not specified. Identifier of the sent message
     *       'inline_message_id'  => '',  // string     - (Optional|Required). Required if chat_id and message_id are not specified. Identifier of the inline message
     *       'reply_markup'       => '',  // string     - (Optional). A JSON-serialized object for a new inline keyboard.
     * ]
     * </code>
     *
     * @link https://core.telegram.org/bots/api#stopmessagelivelocation
     *
     * @return MessageObject|bool
     *
     * @throws TelegramSDKException
     */
    public function stopMessageLiveLocation(array $params): MessageObject
    {
        $response = $this->post('stopMessageLiveLocation', $params);

        return new MessageObject($response->getDecodedBody());
    }
}
