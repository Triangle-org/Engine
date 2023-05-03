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

namespace support\telegram\Objects\Passport;

use support\telegram\Objects\BaseObject;

/**
 * @link https://core.telegram.org/bots/api#securevalue
 *
 * @property DataCredentials|null $data          (Optional). Credentials for encrypted Telegram Passport data. Available for “personal_details”, “passport”, “driver_license”, “identity_card”, “internal_passport” and “address” types.
 * @property FileCredentials|null $frontSide     (Optional). Credentials for an encrypted document's front side. Available for “passport”, “driver_license”, “identity_card” and “internal_passport”.
 * @property FileCredentials|null $reverseSide   (Optional). Credentials for an encrypted document's reverse side. Available for “driver_license” and “identity_card”.
 * @property FileCredentials|null $selfie        (Optional). Credentials for an encrypted selfie of the user with a document. Available for “passport”, “driver_license”, “identity_card” and “internal_passport”.
 * @property FileCredentials[]|null $translation   (Optional). Credentials for an encrypted translation of the document. Available for “passport”, “driver_license”, “identity_card”, “internal_passport”, “utility_bill”, “bank_statement”, “rental_agreement”, “passport_registration” and “temporary_registration”.
 * @property FileCredentials[]|null $files         (Optional). Credentials for encrypted files. Available for “utility_bill”, “bank_statement”, “rental_agreement”, “passport_registration” and “temporary_registration” types.
 */
class SecureValue extends BaseObject
{
    /**
     * {@inheritdoc}
     */
    public function relations()
    {
        return [
            'data' => DataCredentials::class,
            'front_side' => FileCredentials::class,
            'reverse_side' => FileCredentials::class,
            'selfie' => FileCredentials::class,
            'translation' => [FileCredentials::class],
            'files' => [FileCredentials::class],
        ];
    }
}
