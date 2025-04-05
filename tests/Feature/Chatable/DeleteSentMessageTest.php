<?php

namespace Tests\Feature\Chatable;

use Illuminate\Foundation\Testing\RefreshDatabase;
use TMSPerera\HeadlessChat\Exceptions\InvalidParticipationException;
use TMSPerera\HeadlessChat\Exceptions\MessageOwnershipException;
use Workbench\Database\Factories\ConversationFactory;
use Workbench\Database\Factories\UserFactory;

class DeleteSentMessageTest extends BaseChatableTestCase
{
    use RefreshDatabase;

    public function test_when_delete_by_unrelated_participant()
    {
        $user = UserFactory::new()->createOne();
        $sender = UserFactory::new()->createOne();
        $conversation = ConversationFactory::new()->directMessage()->createOne();
        $senderParticipation = $this->joinConversation(conversation: $conversation, participant: $sender);
        $message = $this->sendMessage(conversation: $conversation, senderParticipation: $senderParticipation);

        $this->expectException(InvalidParticipationException::class);
        $user->deleteSentMessage($message);

        $this->assertNotSoftDeleted($message);
    }

    public function test_when_delete_by_other_participant()
    {
        $sender = UserFactory::new()->createOne();
        $recipient = UserFactory::new()->createOne();
        $conversation = ConversationFactory::new()->directMessage()->createOne();
        $senderParticipation = $this->joinConversation(conversation: $conversation, participant: $sender);
        $this->joinConversation(conversation: $conversation, participant: $recipient);
        $message = $this->sendMessage(conversation: $conversation, senderParticipation: $senderParticipation);

        $this->expectException(MessageOwnershipException::class);
        $recipient->deleteSentMessage($message);

        $this->assertNotSoftDeleted($message);
    }

    public function test_when_deleted_by_sender()
    {
        $sender = UserFactory::new()->createOne();
        $recipient = UserFactory::new()->createOne();
        $conversation = ConversationFactory::new()->directMessage()->createOne();
        $senderParticipation = $this->joinConversation(conversation: $conversation, participant: $sender);
        $this->joinConversation(conversation: $conversation, participant: $recipient);
        $message = $this->sendMessage(conversation: $conversation, senderParticipation: $senderParticipation);

        $sender->deleteSentMessage($message);

        $this->assertSoftDeleted($message);
    }
}
