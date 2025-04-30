<?php

namespace Feature\Chatable;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Chatable\BaseChatableTestCase;
use TMSPerera\HeadlessChat\Contracts\Participant;
use TMSPerera\HeadlessChat\DataTransferObjects\ConversationDto;
use TMSPerera\HeadlessChat\Enums\ConversationType;
use TMSPerera\HeadlessChat\Exceptions\ParticipationAlreadyExistsException;
use TMSPerera\HeadlessChat\Exceptions\ParticipationLimitExceededException;
use TMSPerera\HeadlessChat\HeadlessChatActions;
use Workbench\Database\Factories\ConversationFactory;
use Workbench\Database\Factories\UserFactory;

class GroupConversationTest extends BaseChatableTestCase
{
    use RefreshDatabase;

    public function test_when_create_direct_conversation_with_participants_more_than_two()
    {
        $user1 = UserFactory::new()->createOne();
        $user2 = UserFactory::new()->createOne();
        $user3 = UserFactory::new()->createOne();

        $this->expectException(ParticipationLimitExceededException::class);
        HeadlessChatActions::make()->createConversationAction->handle(
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

        $conversation = HeadlessChatActions::make()->createConversationAction->handle(
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

    public function test_when_joining_group_conversation_with_more_than_two_participants()
    {
        $user1 = UserFactory::new()->createOne();
        $user2 = UserFactory::new()->createOne();
        /** @var Participant $user3 */
        $user3 = UserFactory::new()->createOne();
        $conversation = ConversationFactory::new()->group()->createOne();
        $this->joinConversation(conversation: $conversation, participant: $user1);
        $this->joinConversation(conversation: $conversation, participant: $user2);
        $this->assertDatabaseCount('participations', 2);

        $participation = $user3->joinConversation($conversation);

        $this->assertDatabaseCount('participations', 3);
        $this->assertDatabaseHas('participations', [
            'id' => $participation->id,
            'conversation_id' => $conversation->id,
            'participant_type' => $user3->getMorphClass(),
            'participant_id' => $user3->getKey(),
        ]);
    }

    public function test_when_re_joining_group_conversation()
    {
        /** @var Participant $user */
        $user = UserFactory::new()->createOne();
        $conversation = ConversationFactory::new()->group()->createOne();
        $this->joinConversation(conversation: $conversation, participant: $user);
        $this->assertDatabaseCount('participations', 1);

        $this->expectException(ParticipationAlreadyExistsException::class);
        $user->joinConversation($conversation);

        $this->assertDatabaseCount('participations', 1);
        $this->assertDatabaseHas('participations', [
            'conversation_id' => $conversation->id,
            'participant_type' => $user->getMorphClass(),
            'participant_id' => $user->getKey(),
        ]);
    }
}
