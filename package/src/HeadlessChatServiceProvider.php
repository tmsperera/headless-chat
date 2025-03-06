<?php

namespace Tmsperera\HeadlessChat;

use Illuminate\Support\ServiceProvider;

class HeadlessChatServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/headless-chat.php', 'headless-chat');
    }

    public function boot(): void
    {
        $this->publishesMigrations([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ]);

        $this->publishes([
            __DIR__.'/../config/headless-chat.php' => config_path('headless-chat.php'),
        ]);
    }
}
