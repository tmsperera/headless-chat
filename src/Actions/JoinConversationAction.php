<?php

namespace TMSPerera\HeadlessChat\Actions;

use TMSPerera\HeadlessChat\Contracts\Participant;
use TMSPerera\HeadlessChat\Enums\ConversationType;
use TMSPerera\HeadlessChat\Exceptions\ParticipationAlreadyExistsException;
use TMSPerera\HeadlessChat\Exceptions\ParticipationLimitExceededException;
use TMSPerera\HeadlessChat\Models\Conversation;
use TMSPerera\HeadlessChat\Models\Participation;

class JoinConversationAction
{
    /**
     * @throws ParticipationLimitExceededException
     * @throws ParticipationAlreadyExistsException
     */
    public function handle(
        Participant $participant,
        Conversation $conversation,
        array $participationMetadata = [],
    ): Participation {
        $this->validateParticipation(
            participant: $participant,
            conversation: $conversation,
        );

        return $conversation->participations()->create([
            'participant_type' => $participant->getMorphClass(),
            'participant_id' => $participant->getKey(),
            'metadata' => $participationMetadata,
        ]);
    }

    /**
     * @throws ParticipationLimitExceededException
     * @throws ParticipationAlreadyExistsException
     */
    protected function validateParticipation(
        Participant $participant,
        Conversation $conversation,
    ): void {
        if (
            $conversation->type === ConversationType::DIRECT_MESSAGE
            && $conversation->participations()->count() >= 2
        ) {
            throw new ParticipationLimitExceededException;
        }

        $existingParticipation = $conversation->participations()->firstWhere([
            'conversation_id' => $conversation->getKey(),
            'participant_type' => $participant->getMorphClass(),
            'participant_id' => $participant->getKey(),
        ]);

        if ($existingParticipation) {
            throw new ParticipationAlreadyExistsException;
        }
    }
}
