<?php

namespace TMSPerera\HeadlessChat\Actions;

use TMSPerera\HeadlessChat\Contracts\Participant;
use TMSPerera\HeadlessChat\DataTransferObjects\ConversationDto;
use TMSPerera\HeadlessChat\DataTransferObjects\MessageContentDto;
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
        MessageContentDto $messageContentDto,
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

        $messageDto = new MessageDto(
            messageContentDto: new MessageContentDto(
                type: $messageContentDto->type,
                content: $messageContentDto->content,
                metadata: $messageContentDto->metadata,
            ),
            senderParticipation: $senderParticipation,
        );

        return $this->storeMessageAction->handle($messageDto);
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
