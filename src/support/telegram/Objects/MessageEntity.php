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
 * Class MessageEntity.
 *
 * @link https://core.telegram.org/bots/api#messageentity
 *
 * @property string $type      Type of the entity. Can be “mention” (@username), “hashtag” (#hashtag), “cashtag” ($USD), “bot_command” (/start@jobs_bot), “url” (https://telegram.org), “email” (do-not-reply@telegram.org), “phone_number” (+1-212-555-0123), “bold” (bold text), “italic” (italic text), “underline” (underlined text), “strikethrough” (strikethrough text), “code” (monowidth string), “pre” (monowidth block), “text_link” (for clickable text URLs), “text_mention” (for users without usernames)
 * @property int $offset    Offset in UTF-16 code units to the start of the entity
 * @property int $length    Length of the entity in UTF-16 code units
 * @property string|null $url       (Optional). For "text_link" only, url that will be opened after user taps on the text.
 * @property User|null $user      (Optional). For “text_mention” only, the mentioned user.
 * @property string|null $language  (Optional). For “pre” only, the programming language of the entity text.
 */
class MessageEntity extends BaseObject
{
    /**
     * {@inheritdoc}
     *
     * @return array{user: string}
     */
    public function relations(): array
    {
        return [
            'user' => User::class,
        ];
    }
}
