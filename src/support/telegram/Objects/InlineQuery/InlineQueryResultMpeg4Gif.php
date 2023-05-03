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
 * Class InlineQueryResultMpeg4Gif.
 *
 * Represents a link to a video animation (H.264/MPEG-4 AVC video without sound). By default, this animated MPEG-4
 * file will be sent by the user with optional caption. Alternatively, you can use input_message_content to send a
 * message with the specified content instead of the animation.
 *
 * <code>
 * [
 *   'id'                     => '',  //  string                - Required. Unique identifier for this result, 1-64 bytes
 *   'mpeg4_url'              => '',  //  string                - Required. A valid URL for the MP4 file. File size must not exceed 1MB
 *   'mpeg4_width'            => '',  //  int                   - (Optional). Video width
 *   'mpeg4_height'           => '',  //  int                   - (Optional). Video height
 *   'mpeg4_duration'         => '',  //  int                   - (Optional). Video duration
 *   'thumb_url'              => '',  //  string                - Required. URL of the static thumbnail (jpeg or gif) for the result
 *   'title'                  => '',  //  string                - (Optional). Title for the result
 *   'caption'                => '',  //  string                - (Optional). Caption of the MPEG-4 file to be sent, 0-200 characters
 *   'parse_mode'             => '',  //  string                - (Optional). Send Markdown or HTML, if you want Telegram apps to show bold, italic, fixed-width text or inline URLs in the media caption.
 *   'caption_entities'       => '',  //  array                 - (Optional). List of special entities that appear in the caption, which can be specified instead of parse_mode
 *   'reply_markup'           => '',  //  InlineKeyboardMarkup  - (Optional). Inline keyboard attached to the message
 *   'input_message_content'  => '',  //  InputMessageContent   - (Optional). Content of the message to be sent instead of the photo
 * ]
 * </code>
 *
 * @link https://core.telegram.org/bots/api#inlinequeryresultmpeg4gif
 *
 * @method $this setId(string)Unique identifier for this result, 1-64 bytes
 * @method $this setMpeg4Url(string)A valid URL for the MP4 file. File size must not exceed 1MB
 * @method $this setMpeg4Width(int)(Optional). Video width
 * @method $this setMpeg4Height(int)(Optional). Video height
 * @method $this setMpeg4Duration(int)(Optional). Video duration
 * @method $this setThumbUrl(string)URL of the static thumbnail (jpeg or gif) for the result
 * @method $this setTitle(string)(Optional). Title for the result
 * @method $this setCaption(string)(Optional). Caption of the MPEG-4 file to be sent, 0-200 characters
 * @method $this setParseMode(string)(Optional). Send Markdown or HTML, if you want Telegram apps to show bold, italic, fixed-width text or inline URLs in the media caption.
 * @method $this setReplyMarkup(object)(Optional). Inline keyboard attached to the message
 * @method $this setInputMessageContent(object)(Optional). Content of the message to be sent instead of the photo
 */
class InlineQueryResultMpeg4Gif extends InlineBaseObject
{
    protected $type = 'mpeg4_gif';
}
