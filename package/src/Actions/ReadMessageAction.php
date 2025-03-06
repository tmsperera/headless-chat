<?php

namespace Tmsperera\HeadlessChat\Actions;

use Tmsperera\HeadlessChat\Contracts\Participant;
use Tmsperera\HeadlessChat\Events\MessageReadEvent;
use Tmsperera\HeadlessChat\Exceptions\InvalidParticipationException;
use Tmsperera\HeadlessChat\Exceptions\ReadBySenderException;
use Tmsperera\HeadlessChat\Models\Message;
use Tmsperera\HeadlessChat\Models\Participation;

class ReadMessageAction
{
    /**
     * @throws ReadBySenderException
     * @throws InvalidParticipationException
     */
    public function __invoke(Message $message, Participant $reader): void
    {
        $message->loadMissing('participation');

        $participation = $this->getParticipation($message, $reader);

        if ($message->participation->is($participation)) {
            throw new ReadBySenderException;
        }

        $message->messageReads()->create([
            'participation_id' => $participation->getKey(),
        ]);

        MessageReadEvent::dispatch($message, $reader);
    }

    /**
     * @throws InvalidParticipationException
     */
    protected function getParticipation(Message $message, Participant $reader): Participation
    {
        $message->loadMissing('conversation.participations.participant');

        $participation = $message->conversation->participations
            ->first(function (Participation $participation) use ($reader) {
                return $participation->participant->is($reader);
            });

        if (! $participation) {
            throw new InvalidParticipationException;
        }

        return $participation;
    }
}
