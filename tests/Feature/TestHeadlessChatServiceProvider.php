<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tmsperera\HeadlessChatForLaravel\Models\Conversation;

class TestHeadlessChatServiceProvider extends TestCase
{
    use RefreshDatabase;

    public function testConfig()
    {
        $this->assertIsArray(Config::get('headless-chat'));
    }

    public function testMigrations()
    {
        $this->assertEmpty(Conversation::all());
    }
}