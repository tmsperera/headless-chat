<?php

namespace Tests\Feature\Chatable;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\BaseHeadlessChatTestCase;
use TMSPerera\HeadlessChat\Contracts\Participant;
use TMSPerera\HeadlessChat\DataTransferObjects\MessageDto;
use TMSPerera\HeadlessChat\Enums\ConversationType;
use TMSPerera\HeadlessChat\Models\Conversation;
use TMSPerera\HeadlessChat\Models\Message;
use TMSPerera\HeadlessChat\Models\Participation;
use Workbench\Database\Factories\ConversationFactory;
use Workbench\Database\Factories\UserFactory;

class CreateDirectMessageTest extends BaseHeadlessChatTestCase
{
    use RefreshDatabase;

    public function test_when_no_conversation_exist()
    {
        /** @var Participant $sender */
        $sender = UserFactory::new()->create();
        $recipient = UserFactory::new()->create();
        $messageDto = new MessageDto(
            type: 'text',
            content: 'Hello World!',
        );

        $sender->createDirectMessage(
            recipient: $recipient,
            messageDto: $messageDto,
        );

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
            ->where('content', $messageDto->content)
            ->where('type', $messageDto->type)
            ->firstOrFail();
    }

    public function test_when_conversation_exist()
    {
        /** @var Participant $sender */
        $sender = UserFactory::new()->create();
        $recipient = UserFactory::new()->create();
        $conversation = ConversationFactory::new()->directMessage()->create();
        $senderParticipation = $this->joinConversation(conversation: $conversation, participant: $sender);
        $this->joinConversation(conversation: $conversation, participant: $recipient);
        $messageDto = new MessageDto(
            type: 'text',
            content: 'Hello World!',
        );

        $sender->createDirectMessage(
            recipient: $recipient,
            messageDto: $messageDto,
        );

        $this->assertDatabaseCount('conversations', 1);
        $this->assertDatabaseCount('participations', 2);
        $message = Message::query()
            ->where('conversation_id', $conversation->id)
            ->where('participation_id', $senderParticipation->id)
            ->where('content', $messageDto->content)
            ->where('type', $messageDto->type)
            ->firstOrFail();
    }
}
