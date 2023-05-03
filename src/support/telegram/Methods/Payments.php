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

namespace support\telegram\Methods;

use Illuminate\Support\Arr;
use support\telegram\Exceptions\TelegramSDKException;
use support\telegram\Objects\Message;
use support\telegram\Traits\Http;

/**
 * Trait Payments.
 *
 * @mixin Http
 */
trait Payments
{
    /**
     * Send invoices.
     *
     * <code>
     * $params = [
     *      'chat_id'                        => '',  // int            - Required. Unique identifier for the target chat or username of the target channel (in the format "@channelusername")
     *      'title'                          => '',  // string         - Required. Product name, 1-32 characters
     *      'description'                    => '',  // string         - Required. Product description, 1-255 characters
     *      'payload'                        => '',  // string         - Required. Bot-defined invoice payload, 1-128 bytes. This will not be displayed to the user, use for your internal processes.
     *      'provider_token'                 => '',  // string         - Required. Payments provider token, obtained via Botfather
     *      'currency'                       => '',  // string         - Required. Three-letter ISO 4217 currency code
     *      'prices'                         => '',  // LabeledPrice[] - Required. Price breakdown, a list of components (e.g. product price, tax, discount, delivery cost, delivery tax, bonus, etc.)
     *      'max_tip_amount'                 => '',  // int            - (Optional). The maximum accepted amount for tips in the smallest units of the currency (integer, not float/double). For example, for a maximum tip of US$ 1.45 pass max_tip_amount = 145. See the exp parameter in currencies.json, it shows the number of digits past the decimal point for each currency (2 for the majority of currencies). Defaults to 0
     *      'suggested_tip_amounts'          => '',  // int[]          - (Optional). A JSON-serialized array of suggested amounts of tips in the smallest units of the currency (integer, not float/double). At most 4 suggested tip amounts can be specified. The suggested tip amounts must be positive, passed in a strictly increased order and must not exceed max_tip_amount.
     *      'start_parameter'                => '',  // string         - (Optional). Unique deep-linking parameter. If left empty, forwarded copies of the sent message will have a Pay button, allowing multiple users to pay directly from the forwarded message, using the same invoice. If non-empty, forwarded copies of the sent message will have a URL button with a deep link to the bot (instead of a Pay button), with the value used as the start parameter
     *      'provider_data'                  => '',  // string         - (Optional). JSON-encoded data about the invoice, which will be shared with the payment provider. A detailed description of required fields should be provided by the payment provider.
     *      'photo_url'                      => '',  // string         - (Optional). URL of the product photo for the invoice. Can be a photo of the goods or a marketing image for a service. People like it better when they see what they are paying for.
     *      'photo_size'                     => '',  // int            - (Optional). Photo size
     *      'photo_width'                    => '',  // int            - (Optional). Photo width
     *      'photo_height'                   => '',  // int            - (Optional). Photo height
     *      'need_name'                      => '',  // bool           - (Optional). Pass True, if you require the user's full name to complete the order
     *      'need_phone_number'              => '',  // bool           - (Optional). Pass True, if you require the user's phone number to complete the order
     *      'need_email'                     => '',  // bool           - (Optional). Pass True, if you require the user's email to complete the order
     *      'need_shipping_address'          => '',  // bool           - (Optional). Pass True, if you require the user's shipping address to complete the order
     *      'send_phone_number_to_provider'  => '',  // bool           - (Optional). Pass True, if user's phone number should be sent to provider
     *      'send_email_to_provider'         => '',  // bool           - (Optional). Pass True, if user's email address should be sent to provider
     *      'is_flexible'                    => '',  // bool           - (Optional). Pass True, if the final price depends on the shipping method
     *      'disable_notification'           => '',  // bool           - (Optional). Sends the message silently. Users will receive a notification with no sound.
     *      'protect_content'                => '',  // bool           - (Optional). Protects the contents of the sent message from forwarding and saving
     *      'reply_to_message_id'            => '',  // int            - (Optional). If the message is a reply, ID of the original message
     *      'allow_sending_without_reply     => '',  // boo            - (Optional). Pass True, if the message should be sent even if the specified replied-to message is not found
     *      'reply_markup'                   => '',  // string         - (Optional). A JSON-serialized object for an inline keyboard. If empty, one 'Pay total price' button will be shown. If not empty, the first button must be a Pay button.
     * ]
     * </code>
     *
     * @link https://core.telegram.org/bots/api#sendinvoice
     *
     * @param array $params
     *
     * @throws TelegramSDKException
     *
     * @return Message
     */
    public function sendInvoice(array $params): Message
    {
        $params['prices'] = json_encode(Arr::wrap($params['prices']));
        $response = $this->post('sendInvoice', $params);

        return new Message($response->getDecodedBody());
    }

    /**
     * Reply to shipping queries.
     *
     * <code>
     * $params = [
     *      'shippingQueryId'  => '',  // string           - Required. Unique identifier for the query to be answered
     *      'ok'               => '',  // bool             - Required. Specify True if delivery to the specified address is possible and False if there are any problems (for example, if delivery to the specified address is not possible)
     *      'shippingOptions'  => '',  // ShippingOption[] - (Optional|Required). Required if ok is True. A JSON-serialized array of available shipping options.
     *      'errorMessage'     => '',  // string           - (Optional|Required). Required if ok is False. Error message in human readable form that explains why it is impossible to complete the order (e.g. "Sorry, delivery to your desired address is unavailable'). Telegram will display this message to the user.
     * ]
     * </code>
     *
     * @link https://core.telegram.org/bots/api#answershippingquery
     *
     * @param array $params
     *
     * @throws TelegramSDKException
     *
     * @return bool
     */
    public function answerShippingQuery(array $params): bool
    {
        return $this->post('answerShippingQuery', $params)->getResult();
    }

    /**
     * Reply to pre-checkout queries.
     *
     * <code>
     * $params = [
     *      'preCheckoutQueryId'  => '',  // string - Required. Unique identifier for the query to be answered
     *      'ok'                  => '',  // bool   - Required. Specify True if everything is alright (goods are available, etc.) and the bot is ready to proceed with the order. Use False if there are any problems.
     *      'errorMessage'        => '',  // string - (Optional|Required). Required if ok is False. Error message in human readable form that explains the reason for failure to proceed with the checkout (e.g. "Sorry, somebody just bought the last of our amazing black T-shirts while you were busy filling out your payment details. Please choose a different color or garment!"). Telegram will display this message to the user.
     * ]
     * </code>
     *
     * @link https://core.telegram.org/bots/api#answerprecheckoutquery
     *
     * @param array $params
     *
     * @throws TelegramSDKException
     *
     * @return bool
     */
    public function answerPreCheckoutQuery(array $params): bool
    {
        return $this->post('answerPreCheckoutQuery', $params)->getResult();
    }
}
