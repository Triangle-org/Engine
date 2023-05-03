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

namespace support\telegram\Laravel\Artisan;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use support\telegram\Api;
use support\telegram\BotsManager;
use support\telegram\Exceptions\TelegramSDKException;
use support\telegram\Objects\WebhookInfo;
use Symfony\Component\Console\Helper\TableCell;

class WebhookCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:webhook {bot? : The bot name defined in the config file}
                {--all : To perform actions on all your bots.}
                {--setup : To declare your webhook on Telegram servers. So they can call you.}
                {--remove : To remove your already declared webhook on Telegram servers.}
                {--info : To get the information about your current webhook on Telegram servers.}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ease the Process of setting up and removing webhooks.';

    /** @var Api */
    protected $telegram;

    /** @var BotsManager */
    protected $botsManager;

    /** @var array Bot Config */
    protected $config = [];

    /**
     * WebhookCommand constructor.
     *
     * @param BotsManager $botsManager
     */
    public function __construct(BotsManager $botsManager)
    {
        parent::__construct();

        $this->botsManager = $botsManager;
    }

    /**
     * Execute the console command.
     *
     * @throws TelegramSDKException
     */
    public function handle()
    {
        $bot = $this->hasArgument('bot') ? $this->argument('bot') : null;
        $this->telegram = $this->botsManager->bot($bot);
        $this->config = $this->botsManager->getBotConfig($bot);

        if ($this->option('setup')) {
            $this->setupWebhook();
        }

        if ($this->option('remove')) {
            $this->removeWebHook();
        }

        if ($this->option('info')) {
            $this->getInfo();
        }
    }

    /**
     * Setup Webhook.
     * @throws TelegramSDKException
     */
    protected function setupWebhook()
    {
        $params = ['url' => data_get($this->config, 'webhook_url')];
        $certificatePath = data_get($this->config, 'certificate_path', false);

        if ($certificatePath) {
            $params['certificate'] = $certificatePath;
        }

        $response = $this->telegram->setWebhook($params);
        if ($response) {
            $this->info('Success: Your webhook has been set!');

            return;
        }

        $this->error('Your webhook could not be set!');
    }

    /**
     * Remove Webhook.
     * @throws TelegramSDKException
     */
    protected function removeWebHook()
    {
        if ($this->confirm("Are you sure you want to remove the webhook for {$this->config['bot']}?")) {
            $this->info('Removing webhook...');

            if ($this->telegram->removeWebhook()) {
                $this->info('Webhook removed successfully!');

                return;
            }

            $this->error('Webhook removal failed');
        }
    }

    /**
     * Get Webhook Info.
     * @throws TelegramSDKException
     */
    protected function getInfo()
    {
        $this->alert('Webhook Info');

        if ($this->hasArgument('bot') && !$this->option('all')) {
            $response = $this->telegram->getWebhookInfo();
            $this->makeWebhookInfoResponse($response, $this->argument('bot'));

            return;
        }

        if ($this->option('all')) {
            $bots = $this->botsManager->getConfig('bots');
            collect($bots)->each(function ($bot, $key) {
                $response = $this->botsManager->bot($key)->getWebhookInfo();
                $this->makeWebhookInfoResponse($response, $key);
            });
        }
    }

    /**
     * Make WebhookInfo Response for console.
     *
     * @param WebhookInfo $response
     * @param string $bot
     */
    protected function makeWebhookInfoResponse(WebhookInfo $response, string $bot)
    {
        $rows = $response->map(function ($value, $key) {
            $key = Str::title(str_replace('_', ' ', $key));
            $value = is_bool($value) ? $this->mapBool($value) : $value;

            return compact('key', 'value');
        })->toArray();

        $this->table([
            [new TableCell('Bot: ' . $bot, ['colspan' => 2])],
            ['Key', 'Info'],
        ], $rows);
    }

    /**
     * Map Boolean Value to Yes/No.
     *
     * @param $value
     *
     * @return string
     */
    protected function mapBool($value)
    {
        return $value ? 'Yes' : 'No';
    }
}
