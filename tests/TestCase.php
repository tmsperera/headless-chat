<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Tmsperera\HeadlessChat\Providers\HeadlessChatServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    use DatabaseTransactions;
    use WithWorkbench;
}
