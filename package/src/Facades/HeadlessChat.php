<?php

namespace TMSPerera\HeadlessChat\Facades;

use Illuminate\Support\Facades\Facade;
use TMSPerera\HeadlessChat\Config\HeadlessChatConfig;
use TMSPerera\HeadlessChat\Contracts\Participant;
use TMSPerera\HeadlessChat\DataTransferObjects\ConversationDto;
use TMSPerera\HeadlessChat\DataTransferObjects\MessageDto;
use TMSPerera\HeadlessChat\Models\Conversation;
use TMSPerera\HeadlessChat\Models\Message;
use TMSPerera\HeadlessChat\Models\Participation;
use TMSPerera\HeadlessChat\Models\ReadReceipt;

/**
 * @method static HeadlessChatConfig config()
 * @method static Conversation createConversation(array $participants, ConversationDto $conversationDto)
 * @method static Message createMessage(Conversation $conversation, Participant $sender, MessageDto $messageDto, ?Message $parentMessage = null)
 * @method static Message createDirectMessage(Participant $sender, Participant $recipient, MessageDto $messageDto)
 * @method static ReadReceipt readMessage(Message $message, Participant $reader)
 * @method static Participation joinConversation(Participant $participant, Conversation $conversation, array $participationMetadata = [])
 * @method static void deleteMessage(Message $message, Participation $deleterParticipation)
 * @method static void deleteSentMessage(Message $message, Participation $deleter)
 *
 * @see \TMSPerera\HeadlessChat\HeadlessChat
 */
class HeadlessChat extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \TMSPerera\HeadlessChat\HeadlessChat::class;
    }
}
