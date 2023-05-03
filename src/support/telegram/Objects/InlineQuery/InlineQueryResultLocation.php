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

namespace support\telegram\Objects\InlineQuery;

/**
 * Class InlineQueryResultLocation.
 *
 * Represents a location on a map. By default, the location will be sent by the user.
 * Alternatively, you can use input_message_content to send a message with the specified content instead of the location.
 *
 * <code>
 * [
 *    'id'                     => '',  //  string                - Required. Unique identifier for this result, 1-64 Bytes
 *    'latitude'               => '',  //  float                 - Required. Location latitude in degrees
 *    'longitude'              => '',  //  float                 - Required. Location longitude in degrees
 *    'title'                  => '',  //  string                - Required. Location title
 *    'horizontal_accuracy'    => '',  //  float                 - (Optional). The radius of uncertainty for the location, measured in meters; 0-1500
 *    'live_period'            => '',  //  int                   - (Optional). Period in seconds for which the location can be updated, should be between 60 and 86400.
 *    'heading'                => '',  //  int                   - (Optional). For live locations, a direction in which the user is moving, in degrees. Must be between 1 and 360 if specified.
 *    'proximity_alert_radius' => '',  //  int                   - (Optional). For live locations, a maximum distance for proximity alerts about approaching another chat member, in meters. Must be between 1 and 100000 if specified.
 *    'reply_markup'           => '',  //  InlineKeyboardMarkup  - (Optional). Inline keyboard attached to the message
 *    'input_message_content'  => '',  //  InputMessageContent   - (Optional). Content of the message to be sent instead of the location
 *    'thumb_url'              => '',  //  string                - (Optional). Url of the thumbnail for the result
 *    'thumb_width'            => '',  //  int                   - (Optional). Thumbnail width
 *    'thumb_height'           => '',  //  int                   - (Optional). Thumbnail height
 * ]
 * </code>
 *
 * @link https://core.telegram.org/bots/api#inlinequeryresultlocation
 *
 * @method $this setId(string)Unique identifier for this result, 1-64 Bytes
 * @method $this setLatitude(float)Location latitude in degrees
 * @method $this setLongitude(float)Location longitude in degrees
 * @method $this setTitle(string)Location title
 * @method $this setReplyMarkup(object)(Optional). Inline keyboard attached to the message
 * @method $this setInputMessageContent(object)(Optional). Content of the message to be sent instead of the location
 * @method $this setThumbUrl(string)(Optional). Url of the thumbnail for the result
 * @method $this setThumbWidth(int)(Optional). Thumbnail width
 * @method $this setThumbHeight(int)(Optional). Thumbnail height
 */
class InlineQueryResultLocation extends InlineBaseObject
{
    protected $type = 'location';
}
