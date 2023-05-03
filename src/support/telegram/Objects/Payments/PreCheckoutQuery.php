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

namespace support\telegram\Objects\Payments;

use support\telegram\Objects\BaseObject;
use support\telegram\Objects\User;

/**
 * Class PreCheckoutQuery
 *
 * @link https://core.telegram.org/bots/api#precheckoutquery
 *
 * @property string $id                     Unique query identifier
 * @property User $from                   User who sent the query.
 * @property string $currency               Three-letter ISO 4217 currency code
 * @property int $totalAmount            Total price in the smallest units of the currency (integer, not float/double)
 * @property string $invoicePayload         Bot specified invoice payload
 * @property string|null $shippingOptionId       (Optional). Identifier of the shipping option chosen by the user
 * @property OrderInfo|null $orderInfo              (Optional). Order info provided by the user
 */
class PreCheckoutQuery extends BaseObject
{
    /**
     * {@inheritdoc}
     */
    public function relations()
    {
        return [
            'from' => User::class,
            'order_info' => OrderInfo::class,
        ];
    }

    public function objectType(): ?string
    {
        return $this->findType(['shipping_option_id', 'order_info']);
    }
}
