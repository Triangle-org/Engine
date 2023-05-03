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

namespace support\telegram\Objects\InputContent;

use support\telegram\Objects\InlineQuery\InlineBaseObject;

/**
 * Class InputTextMessageContent.
 *
 * Represents the content of a text message to be sent as the result of an inline query.
 *
 * <code>
 * [
 *   'message_text'              => '',  //  string  - Required. Text of the message to be sent, 1-4096 characters.
 *   'parse_mode'                => '',  //  string  - (Optional). Send Markdown or HTML, if you want Telegram apps to show bold, italic, fixed-width text or inline URLs in your bot's message.
 *   'entities'                  => '',  //  array   - (Optional). List of special entities that appear in the caption, which can be specified instead of parse_mode
 *   'disable_web_page_preview'  => '',  //  bool    - (Optional). Disables link previews for links in the sent message
 * ]
 * </code>
 *
 * @link https://core.telegram.org/bots/api#inputtextmessagecontent
 *
 * @method $this setMessageText(string)Text of the message to be sent, 1-4096 characters.
 * @method $this setParseMode(string)(Optional). Send Markdown or HTML, if you want Telegram apps to show bold, italic, fixed-width text or inline URLs in your bot's message.
 * @method $this setDisableWebPagePreview(bool)(Optional). Disables link previews for links in the sent message
 */
class InputTextMessageContent extends InlineBaseObject
{
}
