<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Orchestra\Testbench\Concerns\WithWorkbench;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    use DatabaseTransactions;
    use WithWorkbench;
}
