<?php

namespace Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\BaseHeadlessChatTestCase;
use TMSPerera\HeadlessChat\DataTransferObjects\ConversationDto;
use TMSPerera\HeadlessChat\Enums\ConversationType;
use TMSPerera\HeadlessChat\Exceptions\ParticipationLimitExceededException;
use TMSPerera\HeadlessChat\HeadlessChat;
use Workbench\Database\Factories\UserFactory;

class CreateGroupConversationTest extends BaseHeadlessChatTestCase
{
    use RefreshDatabase;

    public function test_when_create_direct_conversation_with_participants_more_than_two()
    {
        $user1 = UserFactory::new()->createOne();
        $user2 = UserFactory::new()->createOne();
        $user3 = UserFactory::new()->createOne();

        $this->expectException(ParticipationLimitExceededException::class);
        HeadlessChat::createConversation(
            participants: [$user1, $user2, $user3],
            conversationDto: new ConversationDTO(
                conversationType: ConversationType::DIRECT_MESSAGE,
            ),
        );

        $this->assertDatabaseCount('conversations', 0);
        $this->assertDatabaseCount('participations', 0);
    }

    public function test_when_create_group_conversation_with_participants()
    {
        $user1 = UserFactory::new()->createOne();
        $user2 = UserFactory::new()->createOne();
        $user3 = UserFactory::new()->createOne();

        $conversation = HeadlessChat::createConversation(
            participants: [$user1, $user2, $user3],
            conversationDto: new ConversationDTO(
                conversationType: ConversationType::GROUP,
                metadata: $metadata = [
                    'name' => 'Conversation 1',
                ]
            ),
        );

        $this->assertNotNull($conversation);
        $this->assertDatabaseCount('conversations', 1);
        $this->assertDatabaseCount('participations', 3);
        $this->assertDatabaseHas('conversations', [
            'type' => ConversationType::GROUP,
            'metadata' => $this->castAsJson($metadata),
        ]);
        $this->assertDatabaseHas('participations', [
            'conversation_id' => $conversation->id,
            'participant_type' => $user1->getMorphClass(),
            'participant_id' => $user1->id,
        ]);
        $this->assertDatabaseHas('participations', [
            'conversation_id' => $conversation->id,
            'participant_type' => $user2->getMorphClass(),
            'participant_id' => $user2->id,
        ]);
        $this->assertDatabaseHas('participations', [
            'conversation_id' => $conversation->id,
            'participant_type' => $user3->getMorphClass(),
            'participant_id' => $user3->id,
        ]);
    }
}
