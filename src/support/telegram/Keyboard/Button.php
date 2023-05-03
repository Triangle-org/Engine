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

namespace support\telegram\Keyboard;

/**
 * Class Button.
 *
 * @method $this setRequestContact($boolean)    Optional. If True, the user's phone number will be sent as a contact when the button is pressed. Available in private chats only.
 * @method $this setRequestLocation($boolean)   Optional. If True, the user's current location will be sent when the button is pressed. Available in private chats only.
 * @method $this setUrl($string)                (Inline Button Only) Optional. HTTP url to be opened when button is pressed.
 * @method $this setCallbackData($string)       (Inline Button Only) Optional. Data to be sent in a callback query to the bot when button is pressed.
 * @method $this setSwitchInlineQuery($string)  (Inline Button Only) Optional. If set, pressing the button will prompt the user to select one of their chats, open that chat and insert the bot‘s username and the specified inline query in the input field. Can be empty, in which case just the bot’s username will be inserted.
 * @method $this setSwitchInlineQueryCurrentChat($string)  (Inline Button Only) Optional. If set, pressing the button will insert the bot‘s username and the specified inline query in the current chat's input field. Can be empty, in which case only the bot’s username will be inserted.
 * @method $this setCallbackGame($string)  (Inline Button Only) Optional. Description of the game that will be launched when the user presses the button.
 *
 * @template TKey of array-key
 * @template TValue
 * @extends Base<TKey, TValue>
 */
class Button extends Base
{
    /**
     * Button Label Text.
     *
     * @param string $text
     *
     * @return Button
     */
    public function setText($text): self
    {
        $this->items['text'] = $text;

        return $this;
    }
}
