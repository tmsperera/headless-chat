<?php

namespace Tests\Feature\Chatable;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use TMSPerera\HeadlessChat\Events\MessageReadEvent;
use TMSPerera\HeadlessChat\Exceptions\InvalidParticipationException;
use TMSPerera\HeadlessChat\Exceptions\MessageAlreadyReadException;
use TMSPerera\HeadlessChat\Exceptions\ReadBySenderException;
use TMSPerera\HeadlessChat\Models\ReadReceipt;
use Workbench\Database\Factories\ConversationFactory;
use Workbench\Database\Factories\MessageFactory;
use Workbench\Database\Factories\UserFactory;

class ReadMessageTest extends BaseChatableTestCase
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
        $senderParticipation = $this->joinConversation(conversation: $conversation, participant: $sender);
        $recipientParticipation = $this->joinConversation(conversation: $conversation, participant: $recipient);
        $message = $this->sendMessage(conversation: $conversation, senderParticipation: $senderParticipation);

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
        $senderParticipation = $this->joinConversation(conversation: $conversation, participant: $sender);
        $this->joinConversation(conversation: $conversation, participant: $recipient);
        $message = $this->sendMessage(conversation: $conversation, senderParticipation: $senderParticipation);

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

    public function test_when_read_again()
    {
        $sender = UserFactory::new()->createOne();
        $recipient = UserFactory::new()->createOne();
        $conversation = ConversationFactory::new()->directMessage()->createOne();
        $senderParticipation = $this->joinConversation(conversation: $conversation, participant: $sender);
        $recipientParticipation = $this->joinConversation(conversation: $conversation, participant: $recipient);
        $message = $this->sendMessage(conversation: $conversation, senderParticipation: $senderParticipation);
        $this->readMessage($message, $recipientParticipation);

        $this->expectException(MessageAlreadyReadException::class);
        $recipient->readMessage($message);

        Event::assertNotDispatched(MessageReadEvent::class);
    }
}
