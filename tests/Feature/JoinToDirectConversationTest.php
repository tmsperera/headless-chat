<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use TMSPerera\HeadlessChat\Actions\JoinConversationAction;
use TMSPerera\HeadlessChat\Exceptions\ParticipationLimitExceededException;
use Workbench\Database\Factories\ConversationFactory;
use Workbench\Database\Factories\ParticipationFactory;
use Workbench\Database\Factories\UserFactory;

class JoinToDirectConversationTest extends TestCase
{
    use RefreshDatabase;

    public function test_when_conversation_has_no_participants()
    {
        $conversation = ConversationFactory::new()->directMessage()->create();
        $user = UserFactory::new()->create();

        $addParticipantToConversation = $this->app->make(JoinConversationAction::class);
        $addParticipantToConversation($user, $conversation);

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
        ParticipationFactory::new()
            ->forConversation($conversation)
            ->forParticipant($existingUser)
            ->create();
        $user = UserFactory::new()->create();

        $addParticipantToConversation = $this->app->make(JoinConversationAction::class);
        $addParticipantToConversation($user, $conversation);

        $this->assertDatabaseCount('participations', 2);
        $this->assertDatabaseHas('participations', [
            'conversation_id' => $conversation->getKey(),
            'participant_type' => $user::class,
            'participant_id' => $user->getKey(),
        ]);
    }

    public function test_when_conversation_has_two_participant()
    {
        $this->expectException(ParticipationLimitExceededException::class);
        $conversation = ConversationFactory::new()->directMessage()->create();
        $user1 = UserFactory::new()->create();
        $user2 = UserFactory::new()->create();
        ParticipationFactory::new()
            ->forConversation($conversation)
            ->forParticipant($user1)
            ->create();
        ParticipationFactory::new()
            ->forConversation($conversation)
            ->forParticipant($user2)
            ->create();
        $user3 = UserFactory::new()->create();

        $addParticipantToConversation = $this->app->make(JoinConversationAction::class);
        $addParticipantToConversation($user3, $conversation);
    }
}
