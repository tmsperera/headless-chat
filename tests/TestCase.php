<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Orchestra\Testbench\Concerns\WithWorkbench;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    use DatabaseTransactions;
    use WithWorkbench;

    protected function getPackageProviders($app): array
    {
        return [
            'Tmsperera\HeadlessChatForLaravel\Providers\HeadlessChatServiceProvider',
        ];
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../headless-chat/database/migrations');
    }
}
