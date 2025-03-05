<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tmsperera\HeadlessChat\Enums\ConversationType;
use Tmsperera\HeadlessChat\Usecases\CreateConversation;

class TestCreateConversation extends TestCase
{
    use RefreshDatabase;

    public function test_create_conversation()
    {
        $createConversation = $this->app->make(CreateConversation::class);

        $createConversation(ConversationType::DIRECT_MESSAGE);

        $this->assertDatabaseCount('conversations', 1);
        $this->assertDatabaseHas('conversations', ['type' => ConversationType::DIRECT_MESSAGE]);
    }
}
