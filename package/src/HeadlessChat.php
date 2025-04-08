<?php

namespace TMSPerera\HeadlessChat;

use TMSPerera\HeadlessChat\Actions\CreateConversationAction;
use TMSPerera\HeadlessChat\Actions\CreateDirectMessageAction;
use TMSPerera\HeadlessChat\Actions\CreateMessageAction;
use TMSPerera\HeadlessChat\Actions\DeleteMessageAction;
use TMSPerera\HeadlessChat\Actions\DeleteSentMessageAction;
use TMSPerera\HeadlessChat\Actions\JoinConversationAction;
use TMSPerera\HeadlessChat\Actions\ReadMessageAction;
use TMSPerera\HeadlessChat\Config\HeadlessChatConfig;
use TMSPerera\HeadlessChat\Contracts\Participant;
use TMSPerera\HeadlessChat\DataTransferObjects\MessageDto;
use TMSPerera\HeadlessChat\Enums\ConversationType;
use TMSPerera\HeadlessChat\Exceptions\InvalidParticipationException;
use TMSPerera\HeadlessChat\Exceptions\MessageAlreadyReadException;
use TMSPerera\HeadlessChat\Exceptions\MessageOwnershipException;
use TMSPerera\HeadlessChat\Exceptions\ParticipationLimitExceededException;
use TMSPerera\HeadlessChat\Exceptions\ReadBySenderException;
use TMSPerera\HeadlessChat\Models\Conversation;
use TMSPerera\HeadlessChat\Models\Message;
use TMSPerera\HeadlessChat\Models\Participation;
use TMSPerera\HeadlessChat\Models\ReadReceipt;

class HeadlessChat
{
    public function __construct(
        protected HeadlessChatConfig $headlessChatConfig,
        protected CreateConversationAction $createConversationAction,
        protected CreateMessageAction $createMessageAction,
        protected CreateDirectMessageAction $createDirectMessageAction,
        protected ReadMessageAction $readMessageAction,
        protected JoinConversationAction $joinConversationAction,
        protected DeleteMessageAction $deleteMessageAction,
        protected DeleteSentMessageAction $deleteSentMessageAction,
    ) {}

    public function config(): HeadlessChatConfig
    {
        return $this->headlessChatConfig;
    }

    /**
     * @throws ParticipationLimitExceededException
     */
    public function createConversation(
        array $participants,
        ConversationType $conversationType,
        array $conversationMetadata = [],
    ): Conversation {
        return ($this->createConversationAction)(
            participants: $participants,
            conversationType: $conversationType,
            conversationMetadata: $conversationMetadata,
        );
    }

    /**
     * @throws InvalidParticipationException
     */
    public function createMessage(
        Conversation $conversation,
        Participant $sender,
        MessageDto $messageDto,
        ?Message $parentMessage = null,
    ): Message {
        return ($this->createMessageAction)(
            conversation: $conversation,
            sender: $sender,
            messageDto: $messageDto,
            parentMessage: $parentMessage,
        );
    }

    /**
     * @throws InvalidParticipationException
     * @throws ParticipationLimitExceededException
     */
    public function createDirectMessage(
        Participant $sender,
        Participant $recipient,
        MessageDto $messageDto,
    ): Message {
        return ($this->createDirectMessageAction)(
            sender: $sender,
            recipient: $recipient,
            messageDto: $messageDto,
        );
    }

    /**
     * @throws ReadBySenderException
     * @throws InvalidParticipationException
     * @throws MessageAlreadyReadException
     */
    public function readMessage(
        Message $message,
        Participant $reader
    ): ReadReceipt {
        return ($this->readMessageAction)(message: $message, reader: $reader);
    }

    /**
     * @throws ParticipationLimitExceededException
     */
    public function joinConversation(
        Participant $participant,
        Conversation $conversation,
        array $participationMetadata = [],
    ): Participation {
        return ($this->joinConversationAction)(
            participant: $participant,
            conversation: $conversation,
            participationMetadata: $participationMetadata,
        );
    }

    /**
     * @throws InvalidParticipationException
     */
    public function deleteMessage(
        Message $message,
        Participation $deleterParticipation,
    ): void {
        ($this->deleteMessageAction)(message: $message, deleterParticipation: $deleterParticipation);
    }

    /**
     * @throws InvalidParticipationException
     * @throws MessageOwnershipException
     */
    public function deleteSentMessage(
        Message $message,
        Participant $deleter,
    ): void {
        ($this->deleteSentMessageAction)(message: $message, deleter: $deleter);
    }
}
