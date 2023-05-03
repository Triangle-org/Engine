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

/**
 * @link https://core.telegram.org/bots/api#passportscopeelementone
 *
 * @property string $type            Element type. One of “personal_details”, “passport”, “driver_license”, “identity_card”, “internal_passport”, “address”, “utility_bill”, “bank_statement”, “rental_agreement”, “passport_registration”, “temporary_registration”, “phone_number”, “email”
 * @property bool|null $selfie          (Optional). Use this parameter if you want to request a selfie with the document as well. Available for “passport”, “driver_license”, “identity_card” and “internal_passport”
 * @property bool|null $translation     (Optional). Use this parameter if you want to request a translation of the document as well. Available for “passport”, “driver_license”, “identity_card”, “internal_passport”, “utility_bill”, “bank_statement”, “rental_agreement”, “passport_registration” and “temporary_registration”. Note: We suggest to only request translations after you have received a valid document that requires one.
 * @property bool|null $nativeNames     (Optional). Use this parameter to request the first, last and middle name of the user in the language of the user's country of residence. Available for “personal_details”
 */
class PassportScopeElementOne extends PassportScopeElement
{
}
