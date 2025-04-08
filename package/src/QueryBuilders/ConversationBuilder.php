<?php

namespace TMSPerera\HeadlessChat\QueryBuilders;

use Illuminate\Database\Eloquent\Builder;
use TMSPerera\HeadlessChat\Contracts\Participant;
use TMSPerera\HeadlessChat\Enums\ConversationType;
use TMSPerera\HeadlessChat\HeadlessChatConfig;

class ConversationBuilder extends Builder
{
    public function whereDirectMessage(): static
    {
        return $this->where('type', ConversationType::DIRECT_MESSAGE);
    }

    public function whereHasParticipant(Participant $participant): static
    {
        $participation = HeadlessChatConfig::make()->participationModel();

        return $this->whereHas('participations', function (Builder $query) use ($participant, $participation) {
            $query->whereMorphedTo($participation->participant()->getRelationName(), $participant);
        });
    }
}
