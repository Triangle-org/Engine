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
 * Class InlineQueryResultArticle.
 *
 * Represents a link to an article or web page.
 *
 * <code>
 * [
 *   'id'                     => '', // string                 - Required. Unique identifier for this result, 1-64 Bytes
 *   'title'                  => '', // string                 - Required. Title of the result
 *   'input_message_content'  => '', // InputMessageContent    - Required. Content of the message to be sent.
 *   'reply_markup'           => '', // InlineKeyboardMarkup   - (Optional). Inline keyboard attached to the message
 *   'url'                    => '', // string                 - (Optional). URL of the result
 *   'hide_url'               => '', // bool                   - (Optional). Pass True, if you don't want the URL to be shown in the message
 *   'description'            => '', // string                 - (Optional). Short description of the result
 *   'thumb_url'              => '', // string                 - (Optional). Url of the thumbnail for the result
 *   'thumb_width'            => '', // int                    - (Optional). Thumbnail width
 *   'thumb_height'           => '', // int                    - (Optional). Thumbnail height
 * ]
 * </code>
 *
 * @link https://core.telegram.org/bots/api#inlinequeryresultarticle
 *
 * @method $this setId(string)Unique identifier for this result, 1-64 Bytes
 * @method $this setTitle(string)Title of the result
 * @method $this setInputMessageContent(object)Content of the message to be sent.
 * @method $this setReplyMarkup(object)(Optional). Inline keyboard attached to the message
 * @method $this setUrl(string)(Optional). URL of the result
 * @method $this setHideUrl(bool)(Optional). Pass True, if you don't want the URL to be shown in the message
 * @method $this setDescription(string)(Optional). Short description of the result
 * @method $this setThumbUrl(string)(Optional). Url of the thumbnail for the result
 * @method $this setThumbWidth(int)(Optional). Thumbnail width
 * @method $this setThumbHeight(int)(Optional). Thumbnail height
 */
class InlineQueryResultArticle extends InlineBaseObject
{
    protected $type = 'article';
}
