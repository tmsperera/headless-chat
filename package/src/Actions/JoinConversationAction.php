<?php

namespace TMSPerera\HeadlessChat\Actions;

use TMSPerera\HeadlessChat\Contracts\Participant;
use TMSPerera\HeadlessChat\Enums\ConversationType;
use TMSPerera\HeadlessChat\Exceptions\ParticipationLimitExceededException;
use TMSPerera\HeadlessChat\Models\Conversation;
use TMSPerera\HeadlessChat\Models\Participation;

class JoinConversationAction
{
    /**
     * @throws ParticipationLimitExceededException
     */
    public function __invoke(
        Participant $participant,
        Conversation $conversation,
        array $participationMetadata = [],
    ): Participation {
        $this->validateParticipation($conversation);

        return $conversation->participations()->create([
            'participant_type' => $participant->getMorphClass(),
            'participant_id' => $participant->getKey(),
            'metadata' => $participationMetadata,
        ]);
    }

    /**
     * @throws ParticipationLimitExceededException
     */
    protected function validateParticipation(Conversation $conversation): void
    {
        if (
            $conversation->type == ConversationType::DIRECT_MESSAGE
            && $conversation->participations->count() >= 2
        ) {
            throw new ParticipationLimitExceededException;
        }
    }
}
