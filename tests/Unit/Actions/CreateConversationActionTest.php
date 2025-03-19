<?php

namespace Tests\Unit\Actions;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use TMSPerera\HeadlessChat\Actions\CreateConversationAction;
use TMSPerera\HeadlessChat\Enums\ConversationType;
use TMSPerera\HeadlessChat\Exceptions\ParticipationLimitExceededException;
use TypeError;
use Workbench\Database\Factories\UserFactory;

class CreateConversationActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_when_invalid_participant_provided()
    {
        $this->expectException(TypeError::class);
        $action = $this->app->make(CreateConversationAction::class);
        $action(['invalid type'], ConversationType::DIRECT_MESSAGE);

        $this->assertDatabaseCount('conversations', 0);
        $this->assertDatabaseCount('participations', 0);
    }

    public function test_when_more_than_two_participants_for_direct_conversation()
    {
        $participant1 = UserFactory::new()->create();
        $participant2 = UserFactory::new()->create();
        $participant3 = UserFactory::new()->create();

        $this->expectException(ParticipationLimitExceededException::class);
        $action = $this->app->make(CreateConversationAction::class);
        $action([$participant1, $participant2, $participant3], ConversationType::DIRECT_MESSAGE);

        $this->assertDatabaseCount('conversations', 0);
        $this->assertDatabaseCount('participations', 0);
    }
}
