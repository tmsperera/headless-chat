<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tmsperera\HeadlessChatForLaravel\Models\Conversation;
use Tmsperera\HeadlessChatForLaravel\Models\Message;
use Tmsperera\HeadlessChatForLaravel\Models\Participation;

class TestHeadlessChatServiceProvider extends TestCase
{
    use RefreshDatabase;

    public function test_config()
    {
        $this->assertEquals(
            Conversation::class,
            Config::get('headless-chat.models.conversation')
        );
        $this->assertEquals(
            Participation::class,
            Config::get('headless-chat.models.participation')
        );
        $this->assertEquals(
            Message::class,
            Config::get('headless-chat.models.message')
        );
    }

    public function test_migrations()
    {
        $this->assertEmpty(Conversation::all());
    }
}
