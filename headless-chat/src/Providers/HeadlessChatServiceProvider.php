<?php

namespace Tmsperera\HeadlessChatForLaravel\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Tmsperera\HeadlessChatForLaravel\Models\Conversation;
use Tmsperera\HeadlessChatForLaravel\Models\Message;
use Tmsperera\HeadlessChatForLaravel\Models\Participation;

class HeadlessChatServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/headless-chat.php', 'headless-chat'
        );

        $this->app->bind(Conversation::class, Config::get('headless-chat.models.conversation'));
        $this->app->bind(Participation::class, Config::get('headless-chat.models.participation'));
        $this->app->bind(Message::class, Config::get('headless-chat.models.message'));
    }

    public function boot(): void
    {
        $this->publishesMigrations([
            __DIR__.'/../../database/migrations' => database_path('migrations'),
        ]);

        $this->publishes([
            __DIR__.'/../../config/headless-chat.php' => config_path('headless-chat.php'),
        ]);
    }
}
