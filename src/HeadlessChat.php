<?php

namespace TMSPerera\HeadlessChat;

use Illuminate\Support\Facades\App;
use TMSPerera\HeadlessChat\Contracts\Participant;
use TMSPerera\HeadlessChat\DataTransferObjects\ConversationDto;
use TMSPerera\HeadlessChat\DataTransferObjects\MessageDto;
use TMSPerera\HeadlessChat\Exceptions\InvalidParticipationException;
use TMSPerera\HeadlessChat\Exceptions\MessageAlreadyReadException;
use TMSPerera\HeadlessChat\Exceptions\MessageOwnershipException;
use TMSPerera\HeadlessChat\Exceptions\ParticipationAlreadyExistsException;
use TMSPerera\HeadlessChat\Exceptions\ParticipationLimitExceededException;
use TMSPerera\HeadlessChat\Exceptions\ReadBySenderException;
use TMSPerera\HeadlessChat\Models\Conversation;
use TMSPerera\HeadlessChat\Models\Message;
use TMSPerera\HeadlessChat\Models\Participation;
use TMSPerera\HeadlessChat\Models\ReadReceipt;

readonly class HeadlessChat
{
    public function __construct(
        protected HeadlessChatActions $actions,
        protected HeadlessChatConfig $config,
    ) {}

    public static function make(): static
    {
        return App::make(static::class);
    }

    public static function config(): HeadlessChatConfig
    {
        return static::make()->config;
    }

    /**
     * @throws ParticipationLimitExceededException
     */
    public static function createConversation(
        array $participants,
        ConversationDto $conversationDto,
    ): Conversation {
        return static::make()->actions->createConversationAction->handle(...func_get_args());
    }

    /**
     * @throws InvalidParticipationException
     */
    public static function createMessage(
        Conversation $conversation,
        Participant $sender,
        MessageDto $messageDto,
        ?Message $parentMessage = null,
    ): Message {
        return static::make()->actions->createMessageAction->handle(...func_get_args());
    }

    /**
     * @throws ParticipationLimitExceededException
     */
    public static function createDirectMessage(
        Participant $sender,
        Participant $recipient,
        MessageDto $messageDto,
    ): Message {
        return static::make()->actions->createDirectMessageAction->handle(...func_get_args());
    }

    public static function storeMessage(
        MessageDto $messageDto,
        Participation $senderParticipation,
        ?Message $parentMessage = null,
    ): Message {
        return static::make()->actions->storeMessageAction->handle(...func_get_args());
    }

    /**
     * @throws ReadBySenderException
     * @throws MessageAlreadyReadException
     * @throws InvalidParticipationException
     */
    public static function readMessage(
        Message $message,
        Participant $reader,
    ): ReadReceipt {
        return static::make()->actions->readMessageAction->handle(...func_get_args());
    }

    /**
     * @throws ParticipationLimitExceededException
     * @throws ParticipationAlreadyExistsException
     */
    public static function joinConversation(
        Participant $participant,
        Conversation $conversation,
        array $participationMetadata = [],
    ): Participation {
        return static::make()->actions->joinConversationAction->handle(...func_get_args());
    }

    /**
     * @throws InvalidParticipationException
     */
    public static function deleteMessage(
        Message $message,
        Participation $deleterParticipation,
    ): void {
        static::make()->actions->deleteMessageAction->handle(...func_get_args());
    }

    /**
     * @throws InvalidParticipationException
     * @throws MessageOwnershipException
     */
    public static function deleteSentMessage(
        Message $message,
        Participant $deleter,
    ): void {
        static::make()->actions->deleteSentMessageAction->handle(...func_get_args());
    }
}
