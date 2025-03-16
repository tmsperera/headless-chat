<?php

namespace TMSPerera\HeadlessChat\Collections;

use Illuminate\Database\Eloquent\Collection;
use TMSPerera\HeadlessChat\Contracts\Participant;
use TMSPerera\HeadlessChat\Models\Participation;

class ParticipationCollection extends Collection
{
    public function whereParticipant(Participant $participant): Participation
    {
        return $this->firstOrFail(function (Participation $participation) use ($participant) {
            return $participation->getAttribute('participant_type') == $participant->getMorphClass()
                && $participation->getAttribute('participant_id') == $participant->getKey();
        });
    }
}
