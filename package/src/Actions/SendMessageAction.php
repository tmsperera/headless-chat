<?php

namespace TMSPerera\HeadlessChat\Actions;

use TMSPerera\HeadlessChat\Contracts\Participant;
use TMSPerera\HeadlessChat\DataTransferObjects\MessageDto;
use TMSPerera\HeadlessChat\Events\MessageSentEvent;
use TMSPerera\HeadlessChat\Exceptions\InvalidParticipationException;
use TMSPerera\HeadlessChat\Models\Conversation;
use TMSPerera\HeadlessChat\Models\Message;
use TMSPerera\HeadlessChat\Models\Participation;

class SendMessageAction
{
    /**
     * @param  null|callable(Message):void  $afterMessageCreated
     *
     * @throws InvalidParticipationException
     */
    public function __invoke(
        Conversation $conversation,
        Participant $sender,
        MessageDto $messageDto,
        ?Message $parentMessage = null,
        ?callable $afterMessageCreated = null,
    ): Message {
        $participation = $this->resolveParticipation(participant: $sender, conversation: $conversation);

        $message = $participation->messages()->create([
            'conversation_id' => $conversation->getKey(),
            'parent_id' => $parentMessage?->getKey(),
            'type' => $messageDto->type,
            'content' => $messageDto->content,
            'metadata' => $messageDto->metadata,
        ]);

        if ($afterMessageCreated) {
            $afterMessageCreated($message);
        }

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
