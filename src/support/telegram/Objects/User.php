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
 * Class User.
 *
 * @link https://core.telegram.org/bots/api#user
 *
 * @property int $id                            Unique identifier for this user or bot.
 * @property bool $isBot                         True, if this user is a bot
 * @property string $firstName                     User's or bot's first name.
 * @property string|null $lastName                      (Optional). User's or bot's last name.
 * @property string|null $username                      (Optional). User's or bot's username.
 * @property string|null $languageCode                  (Optional). IETF language tag of the user's language
 * @property bool|null $canJoinGroups                 (Optional). True, if the bot can be invited to groups. Returned only in getMe.
 * @property bool|null $canReadAllGroupMessages       (Optional). True, if privacy mode is disabled for the bot. Returned only in getMe.
 * @property bool|null $supportsInlineQueries         (Optional). True, if the bot supports inline queries. Returned only in getMe.
 */
class User extends BaseObject
{
    /**
     * {@inheritdoc}
     */
    public function relations()
    {
        return [];
    }
}
