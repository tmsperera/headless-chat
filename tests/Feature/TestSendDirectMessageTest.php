<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use Tmsperera\HeadlessChat\Actions\SendDirectMessage;
use Tmsperera\HeadlessChat\Enums\ConversationType;
use Tmsperera\HeadlessChat\Events\MessageSent;
use Tmsperera\HeadlessChat\Models\Conversation;
use Tmsperera\HeadlessChat\Models\Message;
use Tmsperera\HeadlessChat\Models\Participation;
use Workbench\Database\Factories\ConversationFactory;
use Workbench\Database\Factories\ParticipationFactory;
use Workbench\Database\Factories\UserFactory;

class TestSendDirectMessageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([MessageSent::class]);
    }

    public function test_when_no_conversation_exist()
    {
        $sender = UserFactory::new()->create();
        $recipient = UserFactory::new()->create();

        $sendDirectMessage = $this->app->make(SendDirectMessage::class);
        $sendDirectMessage($sender, $recipient, $content = 'test');

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
        $message = Message::query()
            ->where('conversation_id', $conversation->id)
            ->where('participation_id', $senderParticipation->id)
            ->where('content', $content)
            ->firstOrFail();
        Event::assertDispatched(function (MessageSent $event) use ($message) {
            return $event->message->id === $message->id;
        });
    }

    public function test_when_conversation_exist()
    {
        $sender = UserFactory::new()->create();
        $recipient = UserFactory::new()->create();
        $conversation = ConversationFactory::new()->directMessage()->create();
        $senderParticipation = ParticipationFactory::new()
            ->forConversation($conversation)
            ->forParticipant($sender)
            ->create();
        ParticipationFactory::new()
            ->forConversation($conversation)
            ->forParticipant($recipient)
            ->create();

        $sendDirectMessage = $this->app->make(SendDirectMessage::class);
        $sendDirectMessage($sender, $recipient, $content = 'test');

        $this->assertDatabaseCount('conversations', 1);
        $this->assertDatabaseCount('participations', 2);
        $message = Message::query()
            ->where('conversation_id', $conversation->id)
            ->where('participation_id', $senderParticipation->id)
            ->where('content', $content)
            ->firstOrFail();
        Event::assertDispatched(function (MessageSent $event) use ($message) {
            return $event->message->id === $message->id;
        });
    }
}
