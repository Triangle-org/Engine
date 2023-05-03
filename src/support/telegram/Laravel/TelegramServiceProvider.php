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

namespace support\telegram\Laravel;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Foundation\Application as LaravelApplication;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application as LumenApplication;
use support\telegram\Api;
use support\telegram\BotsManager;
use support\telegram\Laravel\Artisan\WebhookCommand;

/**
 * Class TelegramServiceProvider.
 */
class TelegramServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->configure();
        $this->offerPublishing();
        $this->registerBindings();
        $this->registerCommands();
    }

    /**
     * Setup the configuration.
     */
    protected function configure()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/telegram.php', 'telegram');
    }

    /**
     * Setup the resource publishing groups.
     */
    protected function offerPublishing()
    {
        if ($this->app instanceof LaravelApplication && $this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/config/telegram.php' => config_path('telegram.php'),
            ], 'telegram-config');
        } elseif ($this->app instanceof LumenApplication) {
            $this->app->configure('telegram');
        }
    }

    /**
     * Register bindings in the container.
     */
    protected function registerBindings()
    {
        $this->app->singleton(BotsManager::class, static function ($app) {
            return (new BotsManager(config('telegram')))->setContainer($app);
        });
        $this->app->alias(BotsManager::class, 'telegram');

        $this->app->bind(Api::class, static function ($app) {
            return $app[BotsManager::class]->bot();
        });
        $this->app->alias(Api::class, 'telegram.bot');
    }

    /**
     * Register the Artisan commands.
     */
    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                WebhookCommand::class,
            ]);
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [BotsManager::class, Api::class, 'telegram', 'telegram.bot'];
    }
}
