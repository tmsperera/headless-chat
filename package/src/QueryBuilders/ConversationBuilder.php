<?php

namespace TMSPerera\HeadlessChat\QueryBuilders;

use Illuminate\Database\Eloquent\Builder;
use TMSPerera\HeadlessChat\Config\HeadlessChatConfig;
use TMSPerera\HeadlessChat\Contracts\Participant;
use TMSPerera\HeadlessChat\Enums\ConversationType;

class ConversationBuilder extends Builder
{
    public function whereDirectMessage(): static
    {
        return $this->where('type', ConversationType::DIRECT_MESSAGE);
    }

    public function whereHasParticipant(Participant $participant): static
    {
        $participation = HeadlessChatConfig::participationInstance();

        return $this->whereHas('participations', function (Builder $query) use ($participant, $participation) {
            $query->whereMorphedTo($participation->participant()->getRelationName(), $participant);
        });
    }
}
