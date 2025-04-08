<?php

namespace Tests\Feature\Chatable;

use Illuminate\Foundation\Testing\RefreshDatabase;
use TMSPerera\HeadlessChat\Contracts\Participant;
use TMSPerera\HeadlessChat\DataTransferObjects\MessageContentDto;
use TMSPerera\HeadlessChat\Exceptions\InvalidParticipationException;
use Workbench\App\Models\User;
use Workbench\Database\Factories\ConversationFactory;
use Workbench\Database\Factories\UserFactory;

class ReplyToMessageTest extends BaseChatableTestCase
{
    use RefreshDatabase;

    public function test_when_reply_by_sender()
    {
        /** @var User|Participant $user */
        $user = UserFactory::new()->createOne();
        $conversation = ConversationFactory::new()->directMessage()->createOne();
        $participation = $this->joinConversation(conversation: $conversation, participant: $user);
        $parentMessage = $this->sendMessage(conversation: $conversation, senderParticipation: $participation);
        $messageContentDto = new MessageContentDto(
            type: 'text',
            content: 'Hello World!',
            metadata: ['foo' => 'bar'],
        );

        $messageReply = $user->createReplyMessage(
            parentMessage: $parentMessage,
            messageContentDto: $messageContentDto,
        );

        $this->assertTrue($messageReply->parentMessage->is($parentMessage));
        $this->assertTrue($messageReply->is($parentMessage->messages->first()));
        $this->assertDatabaseCount('messages', 2);
        $this->assertDatabaseHas('messages', [
            'parent_id' => $parentMessage->getKey(),
            'conversation_id' => $conversation->getKey(),
            'participation_id' => $participation->getKey(),
            'type' => $messageContentDto->type,
            'content' => $messageContentDto->content,
            'metadata' => $this->castAsJson($messageContentDto->metadata),
        ]);
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
        $messageContentDto = new MessageContentDto(
            type: 'text',
            content: 'Hello World!',
            metadata: ['foo' => 'bar'],
        );

        $messageReply = $participant->createReplyMessage(
            parentMessage: $parentMessage,
            messageContentDto: $messageContentDto,
        );

        $this->assertDatabaseCount('messages', 2);
        $this->assertDatabaseHas('messages', [
            'parent_id' => $parentMessage->getKey(),
            'conversation_id' => $conversation->getKey(),
            'participation_id' => $participation2->getKey(),
            'type' => $messageContentDto->type,
            'content' => $messageContentDto->content,
            'metadata' => $this->castAsJson($messageContentDto->metadata),
        ]);
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
        $messageContentDto = new MessageContentDto(
            type: 'text',
            content: 'Hello World!',
            metadata: ['foo' => 'bar'],
        );

        $this->expectException(InvalidParticipationException::class);
        $user->createReplyMessage(
            parentMessage: $parentMessage,
            messageContentDto: $messageContentDto,
        );

        $this->assertDatabaseCount('messages', 1);
    }
}
