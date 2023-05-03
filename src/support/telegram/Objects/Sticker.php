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
 * Class Sticker.
 *
 * @link https://core.telegram.org/bots/api#sticker
 *
 * @property string $fileId              Unique identifier for this file.
 * @property string $fileUniqueId        Unique identifier for this file, which is supposed to be the same over time and for different bots. Can't be used to download or reuse the file.
 * @property int $width               Sticker width.
 * @property int $height              Sticker height.
 * @property bool $isAnimated          True, if the sticker is animated.
 * @property PhotoSize|null $thumb               (Optional). Sticker thumbnail in .webp or .jpg format.
 * @property string|null $emoji               (Optional). Emoji associated with the sticker
 * @property string|null $setName             (Optional). Name of the sticker set to which the sticker belongs
 * @property MaskPosition|null $maskPosition        (Optional). For mask stickers, the position where the mask should be placed
 * @property int|null $fileSize            (Optional). File size.
 */
class Sticker extends BaseObject
{
    /**
     * {@inheritdoc}
     *
     * @return array{thumb: string, mask_position: string}
     */
    public function relations(): array
    {
        return [
            'thumb' => PhotoSize::class,
            'mask_position' => MaskPosition::class,
        ];
    }
}
