<?php

namespace TMSPerera\HeadlessChat\Actions;

use TMSPerera\HeadlessChat\Contracts\Participant;
use TMSPerera\HeadlessChat\Events\MessageDeletedEvent;
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
    public function __invoke(Message $message, Participant $participant): void
    {
        $message->load(['conversation.participations.participant']);

        $participation = $participant->getParticipationIn($message->conversation);

        if (! $participation) {
            throw new InvalidParticipationException;
        }

        if ($message->participation->isNot($participation)) {
            throw new MessageOwnershipException;
        }

        HeadlessChat::deleteMessage(message: $message, participation: $participation);

        MessageDeletedEvent::dispatch($message, $participation);
    }
}
