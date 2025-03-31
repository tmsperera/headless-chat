<?php

namespace TMSPerera\HeadlessChat\Actions;

use TMSPerera\HeadlessChat\Config\HeadlessChatConfig;
use TMSPerera\HeadlessChat\Contracts\Participant;
use TMSPerera\HeadlessChat\Enums\ConversationType;
use TMSPerera\HeadlessChat\Exceptions\InvalidParticipationException;
use TMSPerera\HeadlessChat\Exceptions\ParticipationLimitExceededException;
use TMSPerera\HeadlessChat\HeadlessChat;
use TMSPerera\HeadlessChat\Models\Conversation;
use TMSPerera\HeadlessChat\Models\Message;

class SendDirectMessageAction
{
    /**
     * @throws ParticipationLimitExceededException
     * @throws InvalidParticipationException
     */
    public function __invoke(
        Participant $sender,
        Participant $recipient,
        string $messageContent,
        array $messageMetadata = [],
    ): Message {
        $conversation = $this->getExistingConversation(sender: $sender, recipient: $recipient)
            ?: HeadlessChat::createConversation(
                participants: [$sender, $recipient],
                conversationType: ConversationType::DIRECT_MESSAGE,
            );

        $conversation->load('participations.participant');

        return HeadlessChat::sendMessage(
            conversation: $conversation,
            sender: $sender,
            messageContent: $messageContent,
            messageMetadata: $messageMetadata,
        );
    }

    protected function getExistingConversation(Participant $sender, Participant $recipient): ?Conversation
    {
        return HeadlessChatConfig::conversationInstance()->newQuery()
            ->whereDirectMessage()
            ->whereHasParticipant($sender)
            ->whereHasParticipant($recipient)
            ->first();
    }
}
