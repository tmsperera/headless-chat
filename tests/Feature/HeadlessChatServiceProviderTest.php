<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use TMSPerera\HeadlessChat\Models\Conversation;
use TMSPerera\HeadlessChat\Models\Message;
use TMSPerera\HeadlessChat\Models\Participation;
use TMSPerera\HeadlessChat\Models\ReadReceipt;

class HeadlessChatServiceProviderTest extends TestCase
{
    use RefreshDatabase;

    public function test_migrations()
    {
        $this->assertEmpty(Conversation::all());
        $this->assertEmpty(Participation::all());
        $this->assertEmpty(Message::all());
        $this->assertEmpty(ReadReceipt::all());
    }
}
