<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Tmsperera\HeadlessChat\Providers\HeadlessChatServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    use DatabaseTransactions;
    use WithWorkbench;

    protected function getPackageProviders($app): array
    {
        return [
            HeadlessChatServiceProvider::class,
        ];
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../package/database/migrations');
    }
}
