<?php

namespace Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\BaseHeadlessChatTestCase;
use TMSPerera\HeadlessChat\DataTransferObjects\ConversationDto;
use TMSPerera\HeadlessChat\Enums\ConversationType;
use TMSPerera\HeadlessChat\Exceptions\ParticipationLimitExceededException;
use TMSPerera\HeadlessChat\HeadlessChat;
use TMSPerera\HeadlessChat\Models\Conversation;
use TypeError;
use Workbench\Database\Factories\UserFactory;

class CreateDirectConversationTest extends BaseHeadlessChatTestCase
{
    use RefreshDatabase;

    public function test_when_invalid_participant_provided()
    {
        $this->expectException(TypeError::class);
        HeadlessChat::createConversation(
            participants: ['invalid participant'],
            conversationDto: new ConversationDTO(conversationType: ConversationType::DIRECT_MESSAGE),
        );

        $this->assertDatabaseCount('conversations', 0);
        $this->assertDatabaseCount('participations', 0);
    }

    public function test_when_more_than_two_participants_for_direct_conversation()
    {
        $participant1 = UserFactory::new()->create();
        $participant2 = UserFactory::new()->create();
        $participant3 = UserFactory::new()->create();

        $this->expectException(ParticipationLimitExceededException::class);
        HeadlessChat::createConversation(
            participants: [$participant1, $participant2, $participant3],
            conversationDto: new ConversationDTO(conversationType: ConversationType::DIRECT_MESSAGE),
        );

        $this->assertDatabaseCount('conversations', 0);
        $this->assertDatabaseCount('participations', 0);
    }

    public function test_when_two_participants_for_direct_conversation()
    {
        $participant1 = UserFactory::new()->create();
        $participant2 = UserFactory::new()->create();

        $conversation = HeadlessChat::createConversation(
            participants: [$participant1, $participant2],
            conversationDto: new ConversationDTO(conversationType: ConversationType::DIRECT_MESSAGE),
        );

        $this->assertDatabaseCount('conversations', 1);
        $this->assertDatabaseCount('participations', 2);
        $this->assertCount(2, $conversation->participations);
        $conversation = Conversation::query()
            ->where('type', ConversationType::DIRECT_MESSAGE)
            ->firstOrFail();
        $this->assertDatabaseHas('participations', [
            'conversation_id' => $conversation->id,
            'participant_type' => $participant1->getMorphClass(),
            'participant_id' => $participant1->id,
        ]);
        $this->assertDatabaseHas('participations', [
            'conversation_id' => $conversation->id,
            'participant_type' => $participant2->getMorphClass(),
            'participant_id' => $participant2->id,
        ]);
    }
}
