<?php

namespace TMSPerera\HeadlessChat\Actions;

use TMSPerera\HeadlessChat\Contracts\Participant;
use TMSPerera\HeadlessChat\Exceptions\InvalidParticipationException;
use TMSPerera\HeadlessChat\Exceptions\MessageAlreadyReadException;
use TMSPerera\HeadlessChat\Exceptions\ReadBySenderException;
use TMSPerera\HeadlessChat\Models\Message;
use TMSPerera\HeadlessChat\Models\Participation;
use TMSPerera\HeadlessChat\Models\ReadReceipt;

class ReadMessageAction
{
    /**
     * @throws InvalidParticipationException
     * @throws ReadBySenderException
     * @throws MessageAlreadyReadException
     */
    public function __invoke(Message $message, Participant $reader): ReadReceipt
    {
        $message->load([
            'participation',
            'conversation.participations.participant',
            'readReceipts.participation',
        ]);

        $participation = $this->getParticipation($message, $reader);

        $this->validateParticipation($message, $participation);

        $this->validateReadReceipts($message, $participation);

        $readReceipt = $message->readReceipts()->create([
            'participation_id' => $participation->getKey(),
        ]);

        return $readReceipt;
    }

    /**
     * @throws InvalidParticipationException
     */
    protected function getParticipation(Message $message, Participant $reader): Participation
    {
        $participation = $message->conversation->participations->whereParticipant($reader)->first();

        if (! $participation) {
            throw new InvalidParticipationException;
        }

        return $participation;
    }

    /**
     * @throws ReadBySenderException
     */
    protected function validateParticipation(Message $message, Participation $readerParticipation): void
    {
        if ($message->participation->is($readerParticipation)) {
            throw new ReadBySenderException;
        }
    }

    /**
     * @throws MessageAlreadyReadException
     */
    protected function validateReadReceipts(Message $message, Participation $readerParticipation): void
    {
        $existingReadReceipt = $message->readReceipts->first(function (ReadReceipt $readReceipt) use ($readerParticipation) {
            return $readReceipt->participation->is($readerParticipation);
        });

        if ($existingReadReceipt) {
            throw new MessageAlreadyReadException;
        }
    }
}
