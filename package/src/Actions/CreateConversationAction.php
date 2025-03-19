<?php

namespace TMSPerera\HeadlessChat\Actions;

use Illuminate\Support\Facades\DB;
use TMSPerera\HeadlessChat\Config\HeadlessChatConfig;
use TMSPerera\HeadlessChat\Contracts\Participant;
use TMSPerera\HeadlessChat\Enums\ConversationType;
use TMSPerera\HeadlessChat\Exceptions\ParticipationLimitExceededException;
use TMSPerera\HeadlessChat\Models\Conversation;

class CreateConversationAction
{
    /**
     * @throws ParticipationLimitExceededException
     */
    public function __invoke(array $participants, ConversationType $conversationType): Conversation
    {
        $this->validate(participants: $participants, conversationType: $conversationType);

        $conversation = DB::transaction(function () use ($participants, $conversationType) {
            $conversation = HeadlessChatConfig::conversationModelClass()::query()
                ->create(['type' => $conversationType]);

            $participations = array_map(function (Participant $participant) {
                return [
                    'participant_type' => $participant->getMorphClass(),
                    'participant_id' => $participant->getKey(),
                ];
            }, $participants);

            $conversation->participations()->createMany($participations);

            return $conversation;
        });

        return $conversation->load('participations');
    }

    /**
     * @throws ParticipationLimitExceededException
     */
    protected function validate(array $participants, ConversationType $conversationType): void
    {
        if (
            $conversationType == ConversationType::DIRECT_MESSAGE
            && count($participants) > 2
        ) {
            throw new ParticipationLimitExceededException;
        }
    }
}
