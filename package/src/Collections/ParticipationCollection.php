<?php

namespace TMSPerera\HeadlessChat\Collections;

use Illuminate\Database\Eloquent\Collection;
use TMSPerera\HeadlessChat\Contracts\Participant;
use TMSPerera\HeadlessChat\Models\Participation;

class ParticipationCollection extends Collection
{
    public function whereParticipant(Participant $participant): static
    {
        return $this->find(function (Participation $participation) use ($participant) {
            return $participation->participant->getMorphClass() == $participant->getMorphClass()
                && $participation->participant->getKey() == $participant->getKey();
        });
    }
}
