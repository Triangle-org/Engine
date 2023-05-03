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
 * @link https://core.telegram.org/bots/api#securedata
 *
 * @property SecureValue|null $personalDetails            (Optional). Credentials for encrypted personal details
 * @property SecureValue|null $passport                   (Optional). Credentials for encrypted passport
 * @property SecureValue|null $internalPassport           (Optional). Credentials for encrypted internal passport
 * @property SecureValue|null $driverLicense              (Optional). Credentials for encrypted driver license
 * @property SecureValue|null $identityCard               (Optional). Credentials for encrypted ID card
 * @property SecureValue|null $address                    (Optional). Credentials for encrypted residential address
 * @property SecureValue|null $utilityBill                (Optional). Credentials for encrypted utility bill
 * @property SecureValue|null $bankStatement              (Optional). Credentials for encrypted bank statement
 * @property SecureValue|null $rentalAgreement            (Optional). Credentials for encrypted rental agreement
 * @property SecureValue|null $passportRegistration       (Optional). Credentials for encrypted registration from internal passport
 * @property SecureValue|null $temporaryRegistration      (Optional). Credentials for encrypted temporary registration
 */
class SecureData extends BaseObject
{
    /**
     * {@inheritdoc}
     */
    public function relations()
    {
        return [
            'personal_details' => SecureValue::class,
            'passport' => SecureValue::class,
            'internal_passport' => SecureValue::class,
            'driver_license' => SecureValue::class,
            'identity_card' => SecureValue::class,
            'address' => SecureValue::class,
            'utility_bill' => SecureValue::class,
            'bank_statement' => SecureValue::class,
            'rental_agreement' => SecureValue::class,
            'passport_registration' => SecureValue::class,
            'temporary_registration' => SecureValue::class,
        ];
    }
}
