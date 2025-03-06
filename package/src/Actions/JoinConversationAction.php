<?php

namespace Tmsperera\HeadlessChat\Actions;

use Tmsperera\HeadlessChat\Contracts\Participant;
use Tmsperera\HeadlessChat\Enums\ConversationType;
use Tmsperera\HeadlessChat\Exceptions\ParticipantLimitExceededException;
use Tmsperera\HeadlessChat\Models\Conversation;
use Tmsperera\HeadlessChat\Models\Participation;

class JoinConversationAction
{
    /**
     * @throws ParticipantLimitExceededException
     */
    public function __invoke(Participant $participant, Conversation $conversation): Participation
    {
        $this->validateParticipation($conversation);

        return $conversation->participations()->create([
            'participant_type' => $participant->getMorphClass(),
            'participant_id' => $participant->getKey(),
        ]);
    }

    /**
     * @throws ParticipantLimitExceededException
     */
    protected function validateParticipation(Conversation $conversation): void
    {
        if (
            $conversation->type == ConversationType::DIRECT_MESSAGE
            && $conversation->participations->count() >= 2
        ) {
            throw new ParticipantLimitExceededException;
        }
    }
}
