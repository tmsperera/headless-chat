<?php

namespace TMSPerera\HeadlessChat\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class HeadlessChatServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/headless-chat.php', 'headless-chat');
    }

    public function boot(): void
    {
        $this->publishMigrations();
        $this->publishConfig();
    }

    protected function publishMigrations(): void
    {
        $configPath = 'database.migrations.update_date_on_publish';

        $originalConfigValue = Config::get($configPath);

        Config::set($configPath, true);

        $this->publishesMigrations([
            __DIR__.'/../../database/migrations' => database_path('migrations'),
        ], 'headless-chat-migrations');

        Config::set($configPath, $originalConfigValue);
    }

    protected function publishConfig(): void
    {
        $this->publishes([
            __DIR__.'/../../config/headless-chat.php' => config_path('headless-chat.php'),
        ], 'headless-chat-config');
    }
}
