<?php

namespace TMSPerera\HeadlessChat\Actions;

use TMSPerera\HeadlessChat\Events\MessageDeletedEvent;
use TMSPerera\HeadlessChat\Exceptions\InvalidParticipationException;
use TMSPerera\HeadlessChat\Models\Message;
use TMSPerera\HeadlessChat\Models\Participation;

class DeleteMessageAction
{
    /**
     * @throws InvalidParticipationException
     */
    public function __invoke(
        Message $message,
        Participation $deleterParticipation,
    ): void {
        $message->loadMissing(['conversation.participations']);

        $conversationParticipation = $message->conversation->participations->find($deleterParticipation);

        if (! $conversationParticipation) {
            throw new InvalidParticipationException;
        }

        $message->delete();

        MessageDeletedEvent::dispatch($message, $deleterParticipation);
    }
}
