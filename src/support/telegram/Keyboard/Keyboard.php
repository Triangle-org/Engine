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
 * Class Keyboard.
 *
 * <code>
 * // For Standard Keyboard
 * $params = [
 *   'keyboard'          => '',
 *   'is_persistent'     => '',
 *   'resize_keyboard'   => '',
 *   'one_time_keyboard' => '',
 *   'selective'         => '',
 * ];
 * </code>
 *
 * OR
 *
 * <code>
 * // For Inline Keyboard
 * $params = [
 *   'inline_keyboard' => '',
 * ];
 * </code>
 *
 * @method $this setIsPersistent($boolean)       Optional. Requests clients to always show the keyboard when the regular keyboard is hidden.
 * @method $this setResizeKeyboard($boolean)     Optional. Requests clients to resize the keyboard vertically for optimal fit.
 * @method $this setOneTimeKeyboard($boolean)    Optional. Requests clients to hide the keyboard as soon as it's been used.
 * @method $this setSelective($boolean)          Optional. Use this parameter if you want to show the keyboard to specific users only.
 *
 * @template TKey of array-key
 * @template TValue
 *
 * @extends Base<TKey, TValue>
 */
final class Keyboard extends Base
{
    /** @var bool Make an Inline Keyboard */
    private bool $inline = false;

    /**
     * Represents one button of an inline keyboard.
     *
     * You must use exactly one of the optional fields.
     * You can also utilise the fluent API to build the params payload.
     *
     * <code>
     * $params = [
     *     'text'                                 => '',
     *     'url'                                  => '',
     *     'login_url'                            => '',
     *     'callback_data'                        => '',
     *     'switch_inline_query'                  => '',
     *     'switch_inline_query_current_chat'     => '',
     *     'callback_game'                        => '',
     *     'pay'                                  => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#inlinekeyboardbutton
     */
    public static function inlineButton(string|array $params = []): string|array|Button
    {
        return self::button($params);
    }

    /**
     * Represents one button of the Reply keyboard.
     *
     * For simple text buttons String can be used instead of an array.
     * You can also utilise the fluent API to build the params payload.
     *
     * <code>
     * $params = 'string'
     *
     * OR
     *
     * $params = [
     *   'text'                 => '',
     *   'request_contact'      => '',
     *   'request_location'     => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#keyboardbutton
     */
    public static function button(string|array $params = []): string|array|Button
    {
        if (is_string($params)) {
            return $params;
        }

        return Button::make($params);
    }

    /**
     * Represents the `ReplyKeyboardRemove` class in the Telegram Bot API, which is used to request clients to remove
     * the custom keyboard and display the default letter-keyboard.
     *
     * <code>
     * $params = [
     *   'remove_keyboard' => true,
     *   'selective'     => false,
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#replykeyboardremove
     */
    public static function remove(array $params = []): self
    {
        return new self(array_merge(['remove_keyboard' => true, 'selective' => false], $params));
    }

    /**
     * Display a reply interface to the user (act as if the user has selected the bots message and tapped "Reply").
     *
     * <code>
     * $params = [
     *   'force_reply' => true,
     *   'selective'   => false,
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#forcereply
     */
    public static function forceReply(array $params = []): self
    {
        return new self(array_merge(['force_reply' => true, 'selective' => false], $params));
    }

    /**
     * Make this keyboard inline, So it appears right next to the message it belongs to.
     *
     * @link https://core.telegram.org/bots/api#inlinekeyboardmarkup
     */
    public function inline(): self
    {
        $this->inline = true;

        return $this;
    }

    /**
     * Determine if it's an inline keyboard.
     */
    public function isInlineKeyboard(): bool
    {
        return $this->inline;
    }

    /**
     * Create a new row in keyboard to add buttons.
     */
    public function row(array $buttons = []): self
    {
        $property = $this->inline ? 'inline_keyboard' : 'keyboard';
        $this->items[$property][] = $buttons;

        return $this;
    }
}
