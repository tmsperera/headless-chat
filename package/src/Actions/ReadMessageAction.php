<?php

namespace TMSPerera\HeadlessChat\Actions;

use TMSPerera\HeadlessChat\Contracts\Participant;
use TMSPerera\HeadlessChat\Events\MessageReadEvent;
use TMSPerera\HeadlessChat\Exceptions\InvalidParticipationException;
use TMSPerera\HeadlessChat\Exceptions\ReadBySenderException;
use TMSPerera\HeadlessChat\Models\Message;
use TMSPerera\HeadlessChat\Models\Participation;

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

        $message->readReceipts()->create([
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
