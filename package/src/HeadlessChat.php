<?php

namespace TMSPerera\HeadlessChat;

use Illuminate\Support\Facades\App;
use TMSPerera\HeadlessChat\Actions\CreateConversationAction;
use TMSPerera\HeadlessChat\Actions\DeleteMessageAction;
use TMSPerera\HeadlessChat\Actions\DeleteSentMessageAction;
use TMSPerera\HeadlessChat\Actions\JoinConversationAction;
use TMSPerera\HeadlessChat\Actions\ReadMessageAction;
use TMSPerera\HeadlessChat\Actions\SendDirectMessageAction;
use TMSPerera\HeadlessChat\Actions\SendMessageAction;
use TMSPerera\HeadlessChat\Contracts\Participant;
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
    /**
     * @throws ParticipationLimitExceededException
     */
    public static function createConversation(
        array $participants,
        ConversationType $conversationType,
        array $conversationMetadata = [],
    ): Conversation {
        /** @var CreateConversationAction $action */
        $action = App::make(CreateConversationAction::class);

        return $action(
            participants: $participants,
            conversationType: $conversationType,
            conversationMetadata: $conversationMetadata,
        );
    }

    /**
     * @throws InvalidParticipationException
     */
    public static function sendMessage(
        Conversation $conversation,
        Participant $sender,
        string $messageContent,
        array $messageMetadata = [],
        ?Message $parentMessage = null,
    ): Message {
        /** @var SendMessageAction $action */
        $action = App::make(SendMessageAction::class);

        return $action(
            conversation: $conversation,
            sender: $sender,
            messageContent: $messageContent,
            messageMetadata: $messageMetadata,
            parentMessage: $parentMessage,
        );
    }

    /**
     * @throws ParticipationLimitExceededException
     * @throws InvalidParticipationException
     */
    public static function sendDirectMessage(
        Participant $sender,
        Participant $recipient,
        string $messageContent,
        array $messageMetadata = [],
    ): Message {
        /** @var SendDirectMessageAction $action */
        $action = App::make(SendDirectMessageAction::class);

        return $action(
            sender: $sender,
            recipient: $recipient,
            messageContent: $messageContent,
            messageMetadata: $messageMetadata,
        );
    }

    /**
     * @throws InvalidParticipationException
     */
    public static function replyToMessage(
        Message $parentMessage,
        Participant $sender,
        string $messageContent,
        array $messageMetadata = [],
    ): Message {
        /** @var SendMessageAction $action */
        $action = App::make(SendMessageAction::class);

        return $action(
            conversation: $parentMessage->conversation,
            sender: $sender,
            messageContent: $messageContent,
            messageMetadata: $messageMetadata,
            parentMessage: $parentMessage,
        );
    }

    /**
     * @throws ReadBySenderException
     * @throws InvalidParticipationException
     * @throws MessageAlreadyReadException
     */
    public static function readMessage(
        Message $message,
        Participant $reader
    ): ReadReceipt {
        /** @var ReadMessageAction $action */
        $action = App::make(ReadMessageAction::class);

        return $action(message: $message, reader: $reader);
    }

    /**
     * @throws ParticipationLimitExceededException
     */
    public static function joinConversation(
        Participant $participant,
        Conversation $conversation,
        array $participationMetadata = [],
    ): Participation {
        /** @var JoinConversationAction $action */
        $action = App::make(JoinConversationAction::class);

        return $action(
            participant: $participant,
            conversation: $conversation,
            participationMetadata: $participationMetadata,
        );
    }

    public static function deleteMessage(
        Message $message,
        Participation $participation,
    ): void {
        /** @var DeleteMessageAction $action */
        $action = App::make(DeleteMessageAction::class);

        $action(message: $message, participation: $participation);
    }

    /**
     * @throws InvalidParticipationException
     * @throws MessageOwnershipException
     */
    public static function deleteSentMessage(
        Message $message,
        Participant $participant,
    ): void {
        /** @var DeleteSentMessageAction $action */
        $action = App::make(DeleteSentMessageAction::class);

        $action(message: $message, participant: $participant);
    }
}
