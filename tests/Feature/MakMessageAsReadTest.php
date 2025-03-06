<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use Tmsperera\HeadlessChat\Actions\MarkMessageAsReadAction;
use Tmsperera\HeadlessChat\Events\MessageReadEvent;
use Tmsperera\HeadlessChat\Exceptions\ReadBySenderException;
use Tmsperera\HeadlessChat\Models\MessageRead;
use Workbench\Database\Factories\ConversationFactory;
use Workbench\Database\Factories\MessageFactory;
use Workbench\Database\Factories\ParticipationFactory;
use Workbench\Database\Factories\UserFactory;

class MakMessageAsReadTest extends TestCase
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

        $markMessageAsRead = $this->app->make(MarkMessageAsReadAction::class);
        $markMessageAsRead($message, $recipient);

        $messageRead = MessageRead::query()
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

        $markMessageAsRead = $this->app->make(MarkMessageAsReadAction::class);
        $this->expectException(ReadBySenderException::class);
        $markMessageAsRead($message, $sender);
        Event::assertNotDispatched(MessageReadEvent::class);
    }
}
