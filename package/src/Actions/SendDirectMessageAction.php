<?php

namespace TMSPerera\HeadlessChat\Actions;

use TMSPerera\HeadlessChat\Config\HeadlessChatConfig;
use TMSPerera\HeadlessChat\Contracts\Participant;
use TMSPerera\HeadlessChat\DataTransferObjects\MessageDto;
use TMSPerera\HeadlessChat\Enums\ConversationType;
use TMSPerera\HeadlessChat\Exceptions\InvalidParticipationException;
use TMSPerera\HeadlessChat\Exceptions\ParticipationLimitExceededException;
use TMSPerera\HeadlessChat\HeadlessChat;
use TMSPerera\HeadlessChat\Models\Conversation;
use TMSPerera\HeadlessChat\Models\Message;

class SendDirectMessageAction
{
    /**
     * @param  null|callable(Message):void  $afterMessageCreated
     *
     * @throws InvalidParticipationException
     * @throws ParticipationLimitExceededException
     */
    public function __invoke(
        Participant $sender,
        Participant $recipient,
        MessageDto $messageDto,
        ?callable $afterMessageCreated = null,
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
            messageDto: $messageDto,
            afterMessageCreated: $afterMessageCreated,
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
