<?php

namespace Feature\Chatable;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\BaseHeadlessChatTestCase;
use TMSPerera\HeadlessChat\Contracts\Participant;
use TMSPerera\HeadlessChat\Exceptions\ParticipationAlreadyExistsException;
use Workbench\Database\Factories\ConversationFactory;
use Workbench\Database\Factories\UserFactory;

class JoinGroupConversationTest extends BaseHeadlessChatTestCase
{
    use RefreshDatabase;

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
