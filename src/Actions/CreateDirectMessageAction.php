<?php

namespace TMSPerera\HeadlessChat\Actions;

use TMSPerera\HeadlessChat\Contracts\Participant;
use TMSPerera\HeadlessChat\DataTransferObjects\ConversationDto;
use TMSPerera\HeadlessChat\DataTransferObjects\MessageDto;
use TMSPerera\HeadlessChat\Enums\ConversationType;
use TMSPerera\HeadlessChat\Exceptions\ParticipationLimitExceededException;
use TMSPerera\HeadlessChat\HeadlessChat;
use TMSPerera\HeadlessChat\Models\Conversation;
use TMSPerera\HeadlessChat\Models\Message;

class CreateDirectMessageAction
{
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
        ) ?: HeadlessChat::createConversation(
            participants: [$sender, $recipient],
            conversationDto: new ConversationDto(conversationType: ConversationType::DIRECT_MESSAGE),
        );

        $conversation->load('participations.participant');
        $senderParticipation = $conversation->getParticipationOf($sender);

        return HeadlessChat::storeMessage(
            messageDto: $messageDto,
            senderParticipation: $senderParticipation,
        );
    }

    protected function getExistingConversation(Participant $sender, Participant $recipient): ?Conversation
    {
        return HeadlessChat::config()->conversationModel()->newQuery()
            ->whereDirectMessage()
            ->whereHasParticipant($sender)
            ->whereHasParticipant($recipient)
            ->first();
    }
}
