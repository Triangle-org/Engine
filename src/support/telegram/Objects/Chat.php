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

use support\telegram\Objects\InputMedia\InputMedia;

/**
 * Class Chat.
 *
 * @link https://core.telegram.org/bots/api#chat
 *
 * @property int $id                           Unique identifier for this chat, not exceeding 1e13 by absolute value.
 * @property string $type                         Type of chat, can be either 'private', 'group', 'supergroup' or 'channel'.
 * @property string|null $title                        (Optional). Title, for channels and group chats.
 * @property string|null $username                     (Optional). Username, for private chats and channels if available
 * @property string|null $firstName                    (Optional). First name of the other party in a private chat
 * @property string|null $lastName                     (Optional). Last name of the other party in a private chat
 * @property InputMedia|null $photo                        (Optional). Chat photo. Returned only in getChat.
 * @property string|null $bio                          (Optional). Bio of the other party in a private chat. Returned only in getChat
 * @property bool|null $hasPrivateForwards           (Optional). True, if privacy settings of the other party in the private chat allows to use tg://user?id=<user_id> links only in chats with the user. Returned only in getChat.
 * @property string|null $description                  (Optional). Description, for groups, supergroups and channel chats. Returned only in getChat.
 * @property string|null $inviteLink                   (Optional). Chat invite link, for groups, supergroups and channel chats. Each administrator in a chat generates their own invite links, so the bot must first generate the link using exportChatInviteLink. Returned only in getChat.
 * @property Message|null $pinnedMessage                (Optional). Pinned message, for groups, supergroups and channels. Returned only in getChat.
 * @property ChatPermissions|null $permissions                  (Optional). Pinned message, for groups, supergroups and channels. Returned only in getChat.
 * @property int|null $slowModeDelay                (Optional). For supergroups, the minimum allowed delay between consecutive messages sent by each unpriviledged user. Returned only in getChat.
 * @property bool|null $hasProtectedContent          (Optional). True, if messages from the chat can't be forwarded to other chats. Returned only in getChat.
 * @property string|null $stickerSetName               (Optional). For supergroups, name of group sticker set. Returned only in getChat.
 * @property bool|null $canSetStickerSet             (Optional). True, if the bot can change the group sticker set. Returned only in getChat.
 * @property int|null $linkedChatId                 (Optional). Unique identifier for the linked chat, i.e. the discussion group identifier for a channel and vice versa; for supergroups and channel chats. This identifier may be greater than 32 bits and some programming languages may have difficulty/silent defects in interpreting it. But it is smaller than 52 bits, so a signed 64 bit integer or double-precision float type are safe for storing this identifier. Returned only in getChat.
 * @property ChatLocation|null $location                     (Optional). For supergroups, the location to which the supergroup is connected. Returned only in getChat.
 */
class Chat extends BaseObject
{
    /**
     * {@inheritdoc}
     */
    public function relations()
    {
        return [
            'photo' => InputMedia::class,
            'pinned_message' => Message::class,
            'permissions' => ChatPermissions::class,
            'location' => ChatLocation::class,
        ];
    }
}
