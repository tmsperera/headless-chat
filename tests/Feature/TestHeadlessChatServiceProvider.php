<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tmsperera\HeadlessChat\Models\Conversation;
use Tmsperera\HeadlessChat\Models\Message;
use Tmsperera\HeadlessChat\Models\Participation;

class TestHeadlessChatServiceProvider extends TestCase
{
    use RefreshDatabase;

    public function test_migrations()
    {
        $this->assertEmpty(Conversation::all());
        $this->assertEmpty(Participation::all());
        $this->assertEmpty(Message::all());
    }
}
