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
 * Class Game.
 *
 * @link https://core.telegram.org/bots/api#game
 *
 * @property string $title        Title of the game.
 * @property string $description  Description of the game.
 * @property PhotoSize[] $photo        Photo that will be displayed in the game message in chats.
 * @property string|null $text         (Optional). Brief description of the game or high scores included in the game message. Can be automatically edited to include current high scores for the game when the bot calls setGameScore, or manually edited using editMessageText. 0-4096 characters.
 * @property MessageEntity[]|null $textEntities (Optional). Special entities that appear in text, such as usernames, URLs, bot commands, etc.
 * @property Animation|null $animation    (Optional). Animation that will be displayed in the game message in chats. Upload via BotFather.
 */
class Game extends BaseObject
{
    /**
     * {@inheritdoc}
     */
    public function relations()
    {
        return [
            'photo' => [PhotoSize::class],
            'text_entities' => [MessageEntity::class],
            'animation' => Animation::class,
        ];
    }
}
