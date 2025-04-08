<?php

namespace TMSPerera\HeadlessChat\Actions;

use TMSPerera\HeadlessChat\Contracts\Participant;
use TMSPerera\HeadlessChat\DataTransferObjects\MessageContentDto;
use TMSPerera\HeadlessChat\Exceptions\InvalidParticipationException;
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
        MessageContentDto $messageContentDto,
        ?Message $parentMessage = null,
    ): Message {
        $participation = $this->resolveParticipation(participant: $sender, conversation: $conversation);

        return $participation->messages()->create([
            'conversation_id' => $conversation->getKey(),
            'parent_id' => $parentMessage?->getKey(),
            'type' => $messageContentDto->type,
            'content' => $messageContentDto->content,
            'metadata' => $messageContentDto->metadata,
        ]);
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
