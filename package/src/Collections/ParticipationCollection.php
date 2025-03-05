<?php

namespace Tmsperera\HeadlessChat\Collections;

use Illuminate\Database\Eloquent\Collection;
use Tmsperera\HeadlessChat\Contracts\Participant;
use Tmsperera\HeadlessChat\Models\Participation;

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
