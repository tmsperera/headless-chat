<?php

namespace TMSPerera\HeadlessChat\Providers;

use Illuminate\Support\ServiceProvider;

class HeadlessChatServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishesMigrations([
            __DIR__.'/../../database/migrations' => database_path('migrations'),
        ], 'headless-chat');
    }
}
