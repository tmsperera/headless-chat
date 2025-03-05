<?php

namespace Tmsperera\HeadlessChat\QueryBuilders;

use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;
use Tmsperera\HeadlessChat\Contracts\Participant;
use Tmsperera\HeadlessChat\Enums\ConversationType;

class ConversationBuilder extends Builder
{
    public function whereDirectMessage(): ConversationBuilder
    {
        return $this->where('type', ConversationType::DIRECT_MESSAGE);
    }

    public function whereHasAllParticipants(array $participants): ConversationBuilder
    {
        return $this->whereHas('participations', function (Builder $query) use ($participants) {
            foreach ($participants as $participant) {
                if (! $participant instanceof Participant) {
                    throw new InvalidArgumentException;
                }

                $query->whereMorphedTo('participant', $participant);
            }
        });
    }
}
