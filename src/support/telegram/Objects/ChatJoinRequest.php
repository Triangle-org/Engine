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
 * Class ChatJoinRequest.
 *
 * @link https://core.telegram.org/bots/api#chatjoinrequest
 *
 * @property Chat $chat           Chat to which the request was sent.
 * @property User $from           User that sent the join request.
 * @property int $date           Date the request was sent in Unix time.
 * @property string|null $bio            Optional. Bio of the user.
 * @property ChatInviteLink|null $invite_link    Optional. Chat invite link that was used by the user to send the join request.
 */
class ChatJoinRequest extends BaseObject
{
    /**
     * {@inheritdoc}
     *
     * @return array{chat: string, from: string, invite_link: string}
     */
    public function relations(): array
    {
        return [
            'chat' => Chat::class,
            'from' => User::class,
            'invite_link' => ChatInviteLink::class,
        ];
    }
}
