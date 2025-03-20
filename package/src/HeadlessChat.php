<?php

namespace TMSPerera\HeadlessChat;

use Illuminate\Support\Facades\App;
use TMSPerera\HeadlessChat\Actions\CreateConversationAction;
use TMSPerera\HeadlessChat\Actions\DeleteMessageAction;
use TMSPerera\HeadlessChat\Actions\DeleteSentMessageAction;
use TMSPerera\HeadlessChat\Actions\JoinConversationAction;
use TMSPerera\HeadlessChat\Actions\ReadMessageAction;
use TMSPerera\HeadlessChat\Actions\SendDirectMessageAction;
use TMSPerera\HeadlessChat\Contracts\Participant;
use TMSPerera\HeadlessChat\Enums\ConversationType;
use TMSPerera\HeadlessChat\Exceptions\InvalidParticipationException;
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
    ): Conversation {
        /** @var CreateConversationAction $action */
        $action = App::make(CreateConversationAction::class);

        return $action(participants: $participants, conversationType: $conversationType);
    }

    /**
     * @throws ParticipationLimitExceededException
     */
    public static function sendDirectMessage(
        Participant $sender,
        Participant $recipient,
        string $content,
    ): Message {
        /** @var SendDirectMessageAction $action */
        $action = App::make(SendDirectMessageAction::class);

        return $action(sender: $sender, recipient: $recipient, content: $content);
    }

    /**
     * @throws ReadBySenderException
     * @throws InvalidParticipationException
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
        Conversation $conversation
    ): Participation {
        /** @var JoinConversationAction $action */
        $action = App::make(JoinConversationAction::class);

        return $action(participant: $participant, conversation: $conversation);
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
