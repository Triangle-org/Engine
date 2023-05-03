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
 * @property PassportScopeElementOne[] $oneOf           List of elements one of which must be provided; must contain either several of “passport”, “driver_license”, “identity_card”, “internal_passport” or several of “utility_bill”, “bank_statement”, “rental_agreement”, “passport_registration”, “temporary_registration”
 * @property bool|null $selfie          (Optional). Use this parameter if you want to request a selfie with the document from this list that the user chooses to upload.
 * @property bool|null $translation     (Optional). Use this parameter if you want to request a translation of the document from this list that the user chooses to upload. Note: We suggest to only request translations after you have received a valid document that requires one.
 *
 * @link https://core.telegram.org/bots/api#passportscopeelementoneofseveral
 */
class PassportScopeElementOneOfSeveral extends PassportScopeElement
{
}
