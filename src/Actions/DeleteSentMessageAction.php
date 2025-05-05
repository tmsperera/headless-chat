<?php

namespace TMSPerera\HeadlessChat\Actions;

use TMSPerera\HeadlessChat\Contracts\Participant;
use TMSPerera\HeadlessChat\Exceptions\InvalidParticipationException;
use TMSPerera\HeadlessChat\Exceptions\MessageOwnershipException;
use TMSPerera\HeadlessChat\HeadlessChat;
use TMSPerera\HeadlessChat\Models\Message;

class DeleteSentMessageAction
{
    /**
     * @throws InvalidParticipationException
     * @throws MessageOwnershipException
     */
    public function handle(
        Message $message,
        Participant $deleter,
    ): void {
        $message->loadMissing(['conversation.participations.participant']);

        $participation = $message->conversation->getParticipationOf($deleter);

        if (! $participation) {
            throw new InvalidParticipationException;
        }

        if ($message->participation->isNot($participation)) {
            throw new MessageOwnershipException;
        }

        HeadlessChat::deleteMessage(
            message: $message,
            deleterParticipation: $participation,
        );
    }
}
