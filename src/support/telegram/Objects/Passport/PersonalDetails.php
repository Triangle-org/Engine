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
 * @property string $firstName              First Name
 * @property string $lastName               Last Name
 * @property string|null $middleName             (Optional). Middle Name
 * @property string $birthDate              Date of birth in DD.MM.YYYY format
 * @property string $gender                 Gender, male or female
 * @property string $countryCode            Citizenship (ISO 3166-1 alpha-2 country code)
 * @property string $residenceCountryCode   Country of residence (ISO 3166-1 alpha-2 country code)
 * @property string $firstNameNative        First Name in the language of the user's country of residence
 * @property string $lastNameNative         Last Name in the language of the user's country of residence
 * @property string|null $middleNameNative       (Optional). Middle Name in the language of the user's country of residence
 *
 * @link https://core.telegram.org/bots/api#personaldetails
 */
class PersonalDetails extends BaseObject
{
    /**
     * {@inheritdoc}
     */
    public function relations(): array
    {
        return [
        ];
    }
}
