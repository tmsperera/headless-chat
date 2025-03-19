<?php

namespace Tests\Feature\Chatable;

use Illuminate\Foundation\Testing\RefreshDatabase;
use TMSPerera\HeadlessChat\Exceptions\ParticipationLimitExceededException;
use Workbench\Database\Factories\ConversationFactory;
use Workbench\Database\Factories\UserFactory;

class JoinDirectConversationTest extends BaseChatableTestCase
{
    use RefreshDatabase;

    public function test_when_conversation_has_no_participants()
    {
        $conversation = ConversationFactory::new()->directMessage()->create();
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
        $this->joinConversation($conversation, $existingUser);
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
        $this->joinConversation($conversation, $user1);
        $this->joinConversation($conversation, $user2);
        $user3 = UserFactory::new()->create();

        $this->expectException(ParticipationLimitExceededException::class);
        $user3->joinConversation($conversation);
    }
}
