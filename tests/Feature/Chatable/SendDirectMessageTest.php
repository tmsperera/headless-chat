<?php

namespace Tests\Feature\Chatable;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use TMSPerera\HeadlessChat\Enums\ConversationType;
use TMSPerera\HeadlessChat\Events\MessageSentEvent;
use TMSPerera\HeadlessChat\Models\Conversation;
use TMSPerera\HeadlessChat\Models\Message;
use TMSPerera\HeadlessChat\Models\Participation;
use Workbench\Database\Factories\ConversationFactory;
use Workbench\Database\Factories\UserFactory;

class SendDirectMessageTest extends BaseChatableTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([MessageSentEvent::class]);
    }

    public function test_when_no_conversation_exist()
    {
        $sender = UserFactory::new()->create();
        $recipient = UserFactory::new()->create();

        $sender->sendDirectMessage($recipient, $content = 'test');

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
        Event::assertDispatched(function (MessageSentEvent $event) use ($message) {
            return $event->message->is($message);
        });
    }

    public function test_when_conversation_exist()
    {
        $sender = UserFactory::new()->create();
        $recipient = UserFactory::new()->create();
        $conversation = ConversationFactory::new()->directMessage()->create();
        $senderParticipation = $this->joinConversation($conversation, $sender);
        $this->joinConversation($conversation, $recipient);

        $sender->sendDirectMessage($recipient, $content = 'test');

        $this->assertDatabaseCount('conversations', 1);
        $this->assertDatabaseCount('participations', 2);
        $message = Message::query()
            ->where('conversation_id', $conversation->id)
            ->where('participation_id', $senderParticipation->id)
            ->where('content', $content)
            ->firstOrFail();
        Event::assertDispatched(function (MessageSentEvent $event) use ($message) {
            return $event->message->is($message);
        });
    }
}
