<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Workbench\Database\Factories\ConversationFactory;
use Workbench\Database\Factories\MessageFactory;
use Workbench\Database\Factories\MessageReadFactory;
use Workbench\Database\Factories\ParticipationFactory;
use Workbench\Database\Factories\UserFactory;

class ParticipantUnreadConversationsCountTest extends TestCase
{
    use RefreshDatabase;

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
        MessageFactory::new()
            ->forConversation($conversation)
            ->forParticipation($senderParticipation)
            ->count(2)
            ->create();

        $conversation2 = ConversationFactory::new()->directMessage()->createOne();
        $senderParticipation2 = ParticipationFactory::new()
            ->forConversation($conversation2)
            ->createOne();
        $recipientParticipation2 = ParticipationFactory::new()
            ->forConversation($conversation2)
            ->forParticipant($recipient)
            ->createOne();
        MessageFactory::new()
            ->forConversation($conversation2)
            ->forParticipation($senderParticipation2)
            ->count(3)
            ->create();
        $readMessage = MessageFactory::new()
            ->forConversation($conversation2)
            ->forParticipation($senderParticipation2)
            ->create();

        MessageReadFactory::new()
            ->forMessage($readMessage)
            ->forParticipation($recipientParticipation2)
            ->create();

        $conversation3 = ConversationFactory::new()->directMessage()->createOne();
        ParticipationFactory::new()
            ->forConversation($conversation3)
            ->createOne();
        ParticipationFactory::new()
            ->forConversation($conversation3)
            ->forParticipant($recipient)
            ->createOne();

        dd($recipient->getConversations()->toArray());
        //        dd($recipient->getUnreadConversations()->toArray());
        //        dd($recipient->getUnreadConversationsCount());
        //        dd($recipient->conversationMessages($conversation2)->toArray());
        //        dd($conversation2->messages->toArray());
    }
}
