<?php

namespace Tests\Feature\Chatable;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use TMSPerera\HeadlessChat\Events\MessageReadEvent;
use TMSPerera\HeadlessChat\Exceptions\InvalidParticipationException;
use TMSPerera\HeadlessChat\Exceptions\ReadBySenderException;
use TMSPerera\HeadlessChat\Models\ReadReceipt;
use Workbench\Database\Factories\ConversationFactory;
use Workbench\Database\Factories\MessageFactory;
use Workbench\Database\Factories\ParticipationFactory;
use Workbench\Database\Factories\UserFactory;

class ReadMessageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([MessageReadEvent::class]);
    }

    public function test_when_read_by_other_participant()
    {
        $sender = UserFactory::new()->createOne();
        $recipient = UserFactory::new()->createOne();
        $conversation = ConversationFactory::new()->directMessage()->createOne();
        $senderParticipation = ParticipationFactory::new()
            ->forConversation($conversation)
            ->forParticipant($sender)
            ->createOne();
        $recipientParticipation = ParticipationFactory::new()
            ->forConversation($conversation)
            ->forParticipant($recipient)
            ->createOne();
        $message = MessageFactory::new()
            ->forConversation($conversation)
            ->forParticipation($senderParticipation)
            ->createOne();

        $recipient->readMessage($message);

        $messageRead = ReadReceipt::query()
            ->whereKey($message)
            ->where('participation_id', $recipientParticipation->id)
            ->firstOrFail();
        $this->assertNotNull($messageRead);
        Event::assertDispatched(MessageReadEvent::class, function (MessageReadEvent $event) use ($message, $recipient) {
            return $event->message->is($message)
                && $event->reader->is($recipient);
        });
    }

    public function test_when_read_by_sender()
    {
        $sender = UserFactory::new()->createOne();
        $recipient = UserFactory::new()->createOne();
        $conversation = ConversationFactory::new()->directMessage()->createOne();
        $senderParticipation = ParticipationFactory::new()
            ->forConversation($conversation)
            ->forParticipant($sender)
            ->createOne();
        ParticipationFactory::new()
            ->forConversation($conversation)
            ->forParticipant($recipient)
            ->createOne();
        $message = MessageFactory::new()
            ->forConversation($conversation)
            ->forParticipation($senderParticipation)
            ->createOne();

        $this->expectException(ReadBySenderException::class);
        $sender->readMessage($message);

        Event::assertNotDispatched(MessageReadEvent::class);
    }

    public function test_when_read_by_other_invalid_participant()
    {
        $user = UserFactory::new()->createOne();
        $message = MessageFactory::new()->createOne();

        $this->expectException(InvalidParticipationException::class);
        $user->readMessage($message);

        Event::assertNotDispatched(MessageReadEvent::class);
    }
}
