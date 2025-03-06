<?php

namespace Tmsperera\HeadlessChat\QueryBuilders;

use Illuminate\Database\Eloquent\Builder;
use Tmsperera\HeadlessChat\Contracts\Participant;
use Tmsperera\HeadlessChat\Enums\ConversationType;

class ConversationBuilder extends Builder
{
    public function whereDirectMessage(): ConversationBuilder
    {
        return $this->where('type', ConversationType::DIRECT_MESSAGE);
    }

    public function whereHasParticipant(Participant $participant): ConversationBuilder
    {
        return $this->whereHas('participations', function (Builder $query) use ($participant) {
            $query->whereMorphedTo('participant', $participant);
        });
    }
}
