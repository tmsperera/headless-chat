<?php

namespace TMSPerera\HeadlessChat\Actions;

use TMSPerera\HeadlessChat\Contracts\Participant;
use TMSPerera\HeadlessChat\Events\MessageSentEvent;
use TMSPerera\HeadlessChat\Exceptions\InvalidParticipationException;
use TMSPerera\HeadlessChat\Models\Conversation;
use TMSPerera\HeadlessChat\Models\Message;
use TMSPerera\HeadlessChat\Models\Participation;

class SendMessageAction
{
    /**
     * @throws InvalidParticipationException
     */
    public function __invoke(
        Conversation $conversation,
        Participant $sender,
        string $content,
        array $messageMetadata = [],
        ?Message $parentMessage = null,
    ): Message {
        $participation = $this->resolveParticipation(participant: $sender, conversation: $conversation);

        $message = $participation->messages()->create([
            'conversation_id' => $conversation->getKey(),
            'content' => $content,
            'metadata' => $messageMetadata,
            'parent_id' => $parentMessage?->getKey(),
        ]);

        MessageSentEvent::dispatch($message);

        return $message;
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
