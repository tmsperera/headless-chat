<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tmsperera\HeadlessChat\Enums\ConversationType;
use Tmsperera\HeadlessChat\Models\Conversation;
use Tmsperera\HeadlessChat\Models\Participation;
use Tmsperera\HeadlessChat\Usecases\SendDirectMessage;
use Workbench\Database\Factories\UserFactory;

class TestSendDirectMessage extends TestCase
{
    use RefreshDatabase;

    public function test_sending_message_when_no_conversation()
    {
        $sender = UserFactory::new()->create();
        $recipient = UserFactory::new()->create();

        $sendDirectMessage = $this->app->make(SendDirectMessage::class);
        $sendDirectMessage($sender, $recipient, $message = 'test');

        $this->assertDatabaseCount('conversations', 1);
        $conversation = Conversation::query()
            ->where('type', ConversationType::DIRECT_MESSAGE)
            ->firstOrFail();
        $this->assertDatabaseCount('participations', 2);
        $senderParticipation = Participation::query()
            ->where('conversation_id', $conversation->id)
            ->where('participant_type', $sender->getMorphClass())
            ->where('participant_id', $sender->id)
            ->firstOrFail();
        $this->assertDatabaseHas('participations', [
            'conversation_id' => $conversation->id,
            'participant_type' => $recipient->getMorphClass(),
            'participant_id' => $recipient->id,
        ]);
        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'participation_id' => $senderParticipation->id,
            'content' => $message,
        ]);
    }

    // todo
}
