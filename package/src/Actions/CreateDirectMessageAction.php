<?php

namespace TMSPerera\HeadlessChat\Actions;

use TMSPerera\HeadlessChat\Contracts\Participant;
use TMSPerera\HeadlessChat\DataTransferObjects\ConversationDto;
use TMSPerera\HeadlessChat\DataTransferObjects\MessageDto;
use TMSPerera\HeadlessChat\Enums\ConversationType;
use TMSPerera\HeadlessChat\Exceptions\ParticipationLimitExceededException;
use TMSPerera\HeadlessChat\HeadlessChatConfig;
use TMSPerera\HeadlessChat\Models\Conversation;
use TMSPerera\HeadlessChat\Models\Message;

class CreateDirectMessageAction
{
    public function __construct(
        protected HeadlessChatConfig $headlessChatConfig,
        protected CreateConversationAction $createConversationAction,
        protected StoreMessageAction $storeMessageAction,
    ) {}

    /**
     * @throws ParticipationLimitExceededException
     */
    public function handle(
        Participant $sender,
        Participant $recipient,
        MessageDto $messageDto,
    ): Message {
        $conversation = $this->getExistingConversation(
            sender: $sender,
            recipient: $recipient,
        ) ?: $this->createConversationAction->handle(
            participants: [$sender, $recipient],
            conversationDto: new ConversationDto(conversationType: ConversationType::DIRECT_MESSAGE),
        );

        $conversation->load('participations.participant');
        $senderParticipation = $conversation->getParticipationOf($sender);

        return $this->storeMessageAction->handle(
            messageDto: $messageDto,
            senderParticipation: $senderParticipation,
        );
    }

    protected function getExistingConversation(Participant $sender, Participant $recipient): ?Conversation
    {
        return $this->headlessChatConfig->conversationModel()->newQuery()
            ->whereDirectMessage()
            ->whereHasParticipant($sender)
            ->whereHasParticipant($recipient)
            ->first();
    }
}
