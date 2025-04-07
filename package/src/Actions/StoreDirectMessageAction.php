<?php

namespace TMSPerera\HeadlessChat\Actions;

use TMSPerera\HeadlessChat\Contracts\Participant;
use TMSPerera\HeadlessChat\DataTransferObjects\MessageDto;
use TMSPerera\HeadlessChat\Enums\ConversationType;
use TMSPerera\HeadlessChat\Exceptions\InvalidParticipationException;
use TMSPerera\HeadlessChat\Exceptions\ParticipationLimitExceededException;
use TMSPerera\HeadlessChat\HeadlessChat;
use TMSPerera\HeadlessChat\Models\Conversation;
use TMSPerera\HeadlessChat\Models\Message;

class StoreDirectMessageAction
{
    public function __construct(
        protected HeadlessChat $headlessChat,
    ) {}

    /**
     * @throws InvalidParticipationException
     * @throws ParticipationLimitExceededException
     */
    public function __invoke(
        Participant $sender,
        Participant $recipient,
        MessageDto $messageDto,
    ): Message {
        $conversation = $this->getExistingConversation(sender: $sender, recipient: $recipient)
            ?: $this->headlessChat->createConversation(
                participants: [$sender, $recipient],
                conversationType: ConversationType::DIRECT_MESSAGE,
            );

        $conversation->load('participations.participant');

        return $this->headlessChat->storeMessage(
            conversation: $conversation,
            sender: $sender,
            messageDto: $messageDto,
        );
    }

    protected function getExistingConversation(Participant $sender, Participant $recipient): ?Conversation
    {
        return $this->headlessChat->config()->conversationModel()->newQuery()
            ->whereDirectMessage()
            ->whereHasParticipant($sender)
            ->whereHasParticipant($recipient)
            ->first();
    }
}
