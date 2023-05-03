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
 * Class InlineQueryResultGame.
 *
 * Represents a Game.
 *
 * <code>
 * [
 *   'id'               => '',  //  string                - Required. Unique identifier for this result, 1-64 Bytes.
 *   'game_short_name'  => '',  //  string                - Required. Short name of the game.
 *   'reply_markup'     => '',  //  InlineKeyboardMarkup  - (Optional). Inline keyboard attached to the message
 * ]
 * </code>
 *
 * @link https://core.telegram.org/bots/api#inlinequeryresultgame
 *
 * @method $this setId(string)Unique identifier for this result, 1-64 Bytes.
 * @method $this setGameShortName(string)Short name of the game.
 * @method $this setReplyMarkup(object)(Optional). Inline keyboard attached to the message
 */
class InlineQueryResultGame extends InlineBaseObject
{
    protected $type = 'game';
}
