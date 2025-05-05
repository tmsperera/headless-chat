<?php

namespace TMSPerera\HeadlessChat\Actions;

use TMSPerera\HeadlessChat\Contracts\Participant;
use TMSPerera\HeadlessChat\DataTransferObjects\MessageDto;
use TMSPerera\HeadlessChat\Exceptions\InvalidParticipationException;
use TMSPerera\HeadlessChat\HeadlessChat;
use TMSPerera\HeadlessChat\Models\Conversation;
use TMSPerera\HeadlessChat\Models\Message;
use TMSPerera\HeadlessChat\Models\Participation;

class CreateMessageAction
{
    /**
     * @throws InvalidParticipationException
     */
    public function handle(
        Conversation $conversation,
        Participant $sender,
        MessageDto $messageDto,
        ?Message $parentMessage = null,
    ): Message {
        $senderParticipation = $this->resolveParticipation(participant: $sender, conversation: $conversation);

        return HeadlessChat::storeMessage(
            messageDto: $messageDto,
            senderParticipation: $senderParticipation,
            parentMessage: $parentMessage,
        );
    }

    /**
     * @throws InvalidParticipationException
     */
    protected function resolveParticipation(Participant $participant, Conversation $conversation): Participation
    {
        $conversation->loadMissing('participations.participant');

        $participation = $conversation->participations->whereParticipant($participant)->first();

        if (! $participation) {
            throw new InvalidParticipationException;
        }

        return $participation;
    }
}
