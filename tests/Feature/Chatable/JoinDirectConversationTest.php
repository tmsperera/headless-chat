<?php

namespace Tests\Feature\Chatable;

use Illuminate\Foundation\Testing\RefreshDatabase;
use TMSPerera\HeadlessChat\Contracts\Participant;
use TMSPerera\HeadlessChat\Exceptions\ParticipationLimitExceededException;
use Workbench\Database\Factories\ConversationFactory;
use Workbench\Database\Factories\UserFactory;

class JoinDirectConversationTest extends BaseChatableTestCase
{
    use RefreshDatabase;

    public function test_when_conversation_has_no_participants()
    {
        $conversation = ConversationFactory::new()->directMessage()->create();
        /** @var Participant $user */
        $user = UserFactory::new()->create();

        $user->joinConversation($conversation);

        $this->assertDatabaseCount('participations', 1);
        $this->assertDatabaseHas('participations', [
            'conversation_id' => $conversation->getKey(),
            'participant_type' => $user::class,
            'participant_id' => $user->getKey(),
        ]);
    }

    public function test_when_conversation_has_single_participant()
    {
        $conversation = ConversationFactory::new()->directMessage()->create();
        $existingUser = UserFactory::new()->create();
        $this->joinConversation(conversation: $conversation, participant: $existingUser);
        /** @var Participant $user */
        $user = UserFactory::new()->create();

        $user->joinConversation($conversation);

        $this->assertDatabaseCount('participations', 2);
        $this->assertDatabaseHas('participations', [
            'conversation_id' => $conversation->getKey(),
            'participant_type' => $user::class,
            'participant_id' => $user->getKey(),
        ]);
    }

    public function test_when_conversation_has_two_participant()
    {
        $conversation = ConversationFactory::new()->directMessage()->create();
        $user1 = UserFactory::new()->create();
        $user2 = UserFactory::new()->create();
        /** @var Participant $user3 */
        $user3 = UserFactory::new()->create();
        $this->joinConversation(conversation: $conversation, participant: $user1);
        $this->joinConversation(conversation: $conversation, participant: $user2);

        $this->expectException(ParticipationLimitExceededException::class);
        $user3->joinConversation($conversation);
    }
}
