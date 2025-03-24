<?php

namespace Tests\Feature\Chatable;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use TMSPerera\HeadlessChat\Contracts\Participant;
use TMSPerera\HeadlessChat\Events\MessageSentEvent;
use TMSPerera\HeadlessChat\Exceptions\InvalidParticipationException;
use Workbench\App\Models\User;
use Workbench\Database\Factories\ConversationFactory;
use Workbench\Database\Factories\UserFactory;

class ReplyToMessageTest extends BaseChatableTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([MessageSentEvent::class]);
    }

    public function test_when_reply_by_sender()
    {
        /** @var User|Participant $user */
        $user = UserFactory::new()->createOne();
        $conversation = ConversationFactory::new()->directMessage()->createOne();
        $participation = $this->joinConversation(conversation: $conversation, participant: $user);
        $parentMessage = $this->sendMessage(conversation: $conversation, senderParticipation: $participation);

        $messageReply = $user->replyToMessage(
            parentMessage: $parentMessage,
            content: $content = 'Hello World!',
            messageMetadata: $metadata = ['foo' => 'bar'],
        );

        $this->assertTrue($messageReply->parentMessage->is($parentMessage));
        $this->assertTrue($messageReply->is($parentMessage->replyMessages->first()));
        $this->assertDatabaseCount('messages', 2);
        $this->assertDatabaseHas('messages', [
            'parent_id' => $parentMessage->getKey(),
            'conversation_id' => $conversation->getKey(),
            'participation_id' => $participation->getKey(),
            'content' => $content,
            'metadata' => $this->castAsJson($metadata),
        ]);
        Event::assertDispatched(MessageSentEvent::class, function (MessageSentEvent $event) use ($messageReply, $participation, $parentMessage) {
            return $event->message->is($messageReply)
                && $event->message->participation->is($participation)
                && $event->message->parentMessage->is($parentMessage);
        });
    }

    public function test_when_reply_by_participant()
    {
        /** @var User|Participant $sender */
        $sender = UserFactory::new()->createOne();
        /** @var User|Participant $participant */
        $participant = UserFactory::new()->createOne();
        $conversation = ConversationFactory::new()->directMessage()->createOne();
        $participation1 = $this->joinConversation(conversation: $conversation, participant: $sender);
        $participation2 = $this->joinConversation(conversation: $conversation, participant: $participant);
        $parentMessage = $this->sendMessage(conversation: $conversation, senderParticipation: $participation1);

        $messageReply = $participant->replyToMessage(
            parentMessage: $parentMessage,
            content: $content = 'Hello World!',
            messageMetadata: $metadata = ['foo' => 'bar'],
        );

        $this->assertDatabaseCount('messages', 2);
        $this->assertDatabaseHas('messages', [
            'parent_id' => $parentMessage->getKey(),
            'conversation_id' => $conversation->getKey(),
            'participation_id' => $participation2->getKey(),
            'content' => $content,
            'metadata' => $this->castAsJson($metadata),
        ]);
        Event::assertDispatched(MessageSentEvent::class, function (MessageSentEvent $event) use ($messageReply, $participation2, $parentMessage) {
            return $event->message->is($messageReply)
                && $event->message->participation->is($participation2)
                && $event->message->parentMessage->is($parentMessage);
        });
    }

    public function test_when_reply_to_unknown_conversation()
    {
        /** @var User|Participant $user */
        $user = UserFactory::new()->createOne();
        /** @var User|Participant $sender */
        $sender = UserFactory::new()->createOne();
        $conversation = ConversationFactory::new()->directMessage()->createOne();
        $senderParticipation = $this->joinConversation(conversation: $conversation, participant: $sender);
        $parentMessage = $this->sendMessage(conversation: $conversation, senderParticipation: $senderParticipation);

        $this->expectException(InvalidParticipationException::class);
        $user->replyToMessage(
            parentMessage: $parentMessage,
            content: 'Hello World!',
        );

        $this->assertDatabaseCount('messages', 1);
        Event::assertNotDispatched(MessageSentEvent::class);
    }
}
