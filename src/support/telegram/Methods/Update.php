<?php

namespace support\telegram\Methods;

use League\Event\EmitterInterface;
use support\telegram\Events\UpdateEvent;
use support\telegram\Events\UpdateWasReceived;
use support\telegram\Exceptions\TelegramSDKException;
use support\telegram\FileUpload\InputFile;
use support\telegram\Objects\Update as UpdateObject;
use support\telegram\Objects\WebhookInfo;
use support\telegram\TelegramResponse;
use support\telegram\Traits\Http;

/**
 * Class Update.
 * @mixin Http
 */
trait Update
{
    /**
     * Use this method to receive incoming updates using long polling.
     *
     * <code>
     * $params = [
     *   'offset'  => '',
     *   'limit'   => '',
     *   'timeout' => '',
     *   'allowed_updates' => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#getupdates
     *
     * @param bool $shouldEmitEvents
     * @param array $params [
     *
     * @var int $offset Optional. Identifier of the first update to be returned. Must be greater by one than the highest among the identifiers of previously received updates. By default, updates starting with the earliest unconfirmed update are returned. An update is considered confirmed as soon as getUpdates is called with an offset higher than its update_id. The negative offset can be specified to retrieve updates starting from -offset update from the end of the updates queue. All previous updates will forgotten.
     * @var int $limit Optional. Limits the number of updates to be retrieved. Values between 1—100 are accepted. Defaults to 100.
     * @var int $timeout Optional. Timeout in seconds for long polling. Defaults to 0, i.e. usual short polling. Should be positive, short polling should be used for testing purposes only.
     * @var array $allowed_updates Optional. List the types of updates you want your bot to receive. For example, specify [“message”, “edited_channel_post”, “callback_query”] to only receive updates of these types. See Update for a complete list of available update types. Specify an empty list to receive all updates regardless of type (default). If not specified, the previous setting will be used.
     *
     * ]
     *
     * @throws TelegramSDKException
     *
     * @return UpdateObject[]
     */
    public function getUpdates(array $params = [], $shouldEmitEvents = true): array
    {
        $response = $this->get('getUpdates', $params);

        return collect($response->getResult())
            ->map(function ($data) use ($shouldEmitEvents) {
                $update = new UpdateObject($data);

                if ($shouldEmitEvents) {
                    $this->emitEvent(new UpdateWasReceived($update, $this));
                }
                $this->dispatchUpdateEvent($update);

                return $update;
            })
            ->all();
    }

    /**
     * Set a Webhook to receive incoming updates via an outgoing webhook.
     *
     * <code>
     * $params = [
     *   'url'         => '',
     *   'certificate' => '',
     *   'max_connections' => '',
     *   'allowed_updates' => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#setwebhook
     *
     * @param array $params [
     *
     * @var string $url Required. HTTPS url to send updates to. Use an empty string to remove webhook integration
     * @var InputFile $certificate Optional. Upload your public key certificate so that the root certificate in use can be checked. See our self-signed guide for details.
     * @var int $max_connections Optional. Maximum allowed number of simultaneous HTTPS connections to the webhook for update delivery, 1-100. Defaults to 40. Use lower values to limit the load on your bot‘s server, and higher values to increase your bot’s throughput.
     * @var array $allowed_updates Optional. List the types of updates you want your bot to receive. For example, specify [“message”, “edited_channel_post”, “callback_query”] to only receive updates of these types. See Update for a complete list of available update types. Specify an empty list to receive all updates regardless of type (default). If not specified, the previous setting will be used.
     *
     * ]
     *
     * @throws TelegramSDKException
     *
     * @return bool
     */
    public function setWebhook(array $params): bool
    {
        $this->validateHookUrl($params['url']);

        if (isset($params['certificate'])) {
            $params['certificate'] = $this->formatCertificate($params['certificate']);

            return $this->uploadFile('setWebhook', $params, 'certificate')->getResult();
        }

        return $this->post('setWebhook', $params)->getResult();
    }

    /**
     * Remove webhook integration if you decide to switch back to getUpdates.
     *
     * @link https://core.telegram.org/bots/api#deletewebhook
     *
     * @throws TelegramSDKException
     * @return bool
     */
    public function deleteWebhook(): bool
    {
        return $this->get('deleteWebhook')->getResult();
    }

    /**
     * Get current webhook status.
     *
     * @link https://core.telegram.org/bots/api#getwebhookinfo
     *
     * @throws TelegramSDKException
     * @return WebhookInfo
     */
    public function getWebhookInfo(): WebhookInfo
    {
        /** @var TelegramResponse $response */
        $response = $this->get('getWebhookInfo');

        return new WebhookInfo($response->getDecodedBody());
    }

    /**
     * Returns a webhook update sent by Telegram.
     * Works only if you set a webhook.
     *
     * @param bool $shouldEmitEvent
     * @return UpdateObject
     * @see setWebhook
     *
     */
    public function getWebhookUpdate($shouldEmitEvent = true): UpdateObject
    {
        $body = $this->getRequestBody();

        $update = new UpdateObject($body);

        if ($shouldEmitEvent) {
            $this->emitEvent(new UpdateWasReceived($update, $this));
        }
        $this->dispatchUpdateEvent($update);

        return $update;
    }

    /**
     * Alias for deleteWebhook.
     *
     * @return bool
     * @throws TelegramSDKException
     *
     */
    public function removeWebhook(): bool
    {
        return $this->deleteWebhook();
    }

    /**
     * @param string $url
     *
     * @throws TelegramSDKException
     */
    private function validateHookUrl(string $url)
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new TelegramSDKException('Invalid URL Provided');
        }

        if (parse_url($url, PHP_URL_SCHEME) !== 'https') {
            throw new TelegramSDKException('Invalid URL, should be a HTTPS url.');
        }
    }

    private function formatCertificate($certificate)
    {
        if ($certificate instanceof InputFile) {
            return $certificate;
        }

        return InputFile::create($certificate, 'certificate.pem');
    }

    /**
     * @return mixed
     */
    private function getRequestBody()
    {
        $rawBody = request()->rawBody();
        return json_decode($rawBody, true);
    }

    /** Dispatch Update Event. */
    private function dispatchUpdateEvent(UpdateObject $update): void
    {
        if (!property_exists($this, 'eventEmitter') || !$this->eventEmitter instanceof EmitterInterface) {
            return;
        }

        $eventEmitter = $this->eventEmitter;

        $eventEmitter->emit(new UpdateEvent($this, $update));
        $updateType = $update->objectType();
        if (is_string($updateType)) {
            $eventEmitter->emit(new UpdateEvent($this, $update, $updateType));

            $messageType = $update->getMessage()->objectType();
            if (null !== $messageType) {
                $eventEmitter->emit(new UpdateEvent($this, $update, "$updateType.$messageType"));
            }
        }
    }
}
