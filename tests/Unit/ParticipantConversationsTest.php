<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use TMSPerera\HeadlessChat\Contracts\Participant;
use TMSPerera\HeadlessChat\Models\Conversation;
use TMSPerera\HeadlessChat\Models\Message;
use TMSPerera\HeadlessChat\Models\Participation;
use Workbench\Database\Factories\ConversationFactory;
use Workbench\Database\Factories\MessageFactory;
use Workbench\Database\Factories\ParticipationFactory;
use Workbench\Database\Factories\ReadReceiptFactory;
use Workbench\Database\Factories\UserFactory;

class ParticipantConversationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_when_there_are_unread_messages()
    {
        $user = UserFactory::new()->createOne();
        $sender = UserFactory::new()->createOne();
        $conversation = ConversationFactory::new()->directMessage()->createOne();
        $senderParticipation = $this->joinConversation($sender, $conversation);
        $this->joinConversation($user, $conversation);
        MessageFactory::new()
            ->forConversation($conversation)
            ->forParticipation($senderParticipation)
            ->count(2)
            ->create();

        $conversations = $user->conversationsWithMetrics;

        $this->assertCount(1, $conversations);
        $this->assertEquals(2, $conversations[0]->total_message_count);
        $this->assertEquals(0, $conversations[0]->read_message_count);
        $this->assertEquals(2, $conversations[0]->unread_message_count);
    }

    public function test_when_there_are_read_messages()
    {
        $user = UserFactory::new()->createOne();
        $sender = UserFactory::new()->createOne();
        $conversation = ConversationFactory::new()->directMessage()->createOne();
        $senderParticipation = $this->joinConversation($sender, $conversation);
        $userParticipation = $this->joinConversation($user, $conversation);
        MessageFactory::new()
            ->forConversation($conversation)
            ->forParticipation($senderParticipation)
            ->count(2)
            ->create();
        $readMessage = MessageFactory::new()
            ->forConversation($conversation)
            ->forParticipation($senderParticipation)
            ->createOne();
        ReadReceiptFactory::new()
            ->forMessage($readMessage)
            ->forParticipation($userParticipation)
            ->create();

        $conversations = $user->conversationsWithMetrics;

        $this->assertCount(1, $conversations);
        $this->assertEquals(3, $conversations[0]->total_message_count);
        $this->assertEquals(1, $conversations[0]->read_message_count);
        $this->assertEquals(2, $conversations[0]->unread_message_count);
    }

    public function test_when_there_are_no_messages()
    {
        $user = UserFactory::new()->createOne();
        ConversationFactory::new()
            ->hasParticipations(ParticipationFactory::new()->forParticipant($user))
            ->directMessage()
            ->count(2)
            ->create();

        $conversations = $user->conversationsWithMetrics;

        $this->assertCount(2, $conversations);
        $this->assertEquals(0, $conversations[0]->total_message_count);
        $this->assertEquals(0, $conversations[0]->read_message_count);
        $this->assertEquals(0, $conversations[0]->unread_message_count);
        $this->assertNull($conversations[0]->latest_message_at);
        $this->assertEquals(0, $conversations[1]->total_message_count);
        $this->assertEquals(0, $conversations[1]->read_message_count);
        $this->assertEquals(0, $conversations[1]->unread_message_count);
        $this->assertNull($conversations[1]->latest_message_at);
    }

    public function test_when_there_are_multiple_conversations()
    {
        $user = UserFactory::new()->createOne();
        ConversationFactory::new()
            ->hasParticipations(ParticipationFactory::new()->forParticipant($user))
            ->directMessage()
            ->createOne();
        ConversationFactory::new()
            ->hasParticipations(ParticipationFactory::new()->forParticipant($user))
            ->directMessage()
            ->createOne();

        $conversations = $user->conversationsWithMetrics;

        $this->assertCount(2, $conversations);
        $this->assertEquals(0, $conversations[0]->total_message_count);
        $this->assertEquals(0, $conversations[0]->read_message_count);
        $this->assertEquals(0, $conversations[0]->unread_message_count);
        $this->assertEquals(0, $conversations[1]->total_message_count);
        $this->assertEquals(0, $conversations[1]->read_message_count);
        $this->assertEquals(0, $conversations[1]->unread_message_count);
    }

    public function test_conversation_order()
    {
        $this->travelTo(now()->subHour());
        $sender = UserFactory::new()->createOne();
        $user = UserFactory::new()->createOne();
        // Conversation
        $conversation = ConversationFactory::new()->directMessage()->createOne();
        $this->joinConversation($sender, $conversation);
        $userParticipation = $this->joinConversation($user, $conversation);
        $this->sendMessage($sender, $conversation);
        $readMessage = $this->sendMessage($sender, $conversation);
        ReadReceiptFactory::new()
            ->forMessage($readMessage)
            ->forParticipation($userParticipation)
            ->create();
        // Conversation 2
        $this->travelBack();
        $sender2 = UserFactory::new()->createOne();
        $conversation2 = ConversationFactory::new()->directMessage()->createOne();
        $this->joinConversation($sender2, $conversation2);
        $this->joinConversation($user, $conversation2);
        $lastestMessage = $this->sendMessage($sender2, $conversation2);

        $conversations = $user->conversationsWithMetrics;

        $this->assertCount(2, $conversations);
        $this->assertEquals(1, $conversations[0]->total_message_count);
        $this->assertEquals(0, $conversations[0]->read_message_count);
        $this->assertEquals(1, $conversations[0]->unread_message_count);
        $this->assertEquals($lastestMessage->created_at->toDateTimeString(), $conversations[0]->latest_message_at);
        $this->assertEquals(2, $conversations[1]->total_message_count);
        $this->assertEquals(1, $conversations[1]->read_message_count);
        $this->assertEquals(1, $conversations[1]->unread_message_count);
    }

    public function test_when_there_are_unrelated_conversations()
    {
        $user = UserFactory::new()->createOne();
        $sender = UserFactory::new()->createOne();
        // Conversation
        $conversation = ConversationFactory::new()->directMessage()->createOne();
        $this->joinConversation($sender, $conversation);
        $this->joinConversation($user, $conversation);
        $this->sendMessage($sender, $conversation);
        // Conversation 2
        $conversation2 = ConversationFactory::new()->directMessage()->createOne();
        $this->joinConversation($sender, $conversation2);
        $this->sendMessage($sender, $conversation2);

        $conversations = $user->conversationsWithMetrics;

        $this->assertCount(1, $conversations);
        $this->assertEquals(1, $conversations[0]->total_message_count);
        $this->assertEquals(0, $conversations[0]->read_message_count);
        $this->assertEquals(1, $conversations[0]->unread_message_count);
    }

    protected function joinConversation(Participant $participant, Conversation $conversation): Participation
    {
        return ParticipationFactory::new()
            ->forParticipant($participant)
            ->forConversation($conversation)
            ->createOne();
    }

    protected function sendMessage(Participant $sender, Conversation $conversation): Message
    {
        $sender->refresh();

        $participation = $sender->participations
            ->where('conversation_id', $conversation->getKey())
            ->firstOrFail();

        return MessageFactory::new()
            ->forConversation($conversation)
            ->forParticipation($participation)
            ->createOne();
    }
}
