<?php

namespace Tmsperera\HeadlessChat\Config;

use Illuminate\Support\Facades\Config;
use Tmsperera\HeadlessChat\Models\Conversation;
use Tmsperera\HeadlessChat\Models\Message;
use Tmsperera\HeadlessChat\Models\MessageRead;
use Tmsperera\HeadlessChat\Models\Participation;

class HeadlessChatConfig
{
    /**
     * @return class-string<Conversation>
     */
    public static function conversationModelClass(): string
    {
        return Config::get('headless-chat.models.conversation');
    }

    /**
     * @return class-string<Message>
     */
    public static function messageModelClass(): string
    {
        return Config::get('headless-chat.models.message');
    }

    /**
     * @return class-string<Participation>
     */
    public static function participationModelClass(): string
    {
        return Config::get('headless-chat.models.participation');
    }

    /**
     * @return class-string<MessageRead>
     */
    public static function messageReadModelClass(): string
    {
        return Config::get('headless-chat.models.message_read');
    }

    public static function newConversationModel(): Conversation
    {
        return new (static::conversationModelClass());
    }

    public static function newMessageModel(): Message
    {
        return new (static::messageModelClass());
    }

    public static function newParticipationModel(): Participation
    {
        return new (static::participationModelClass());
    }

    public static function newMessageReadModel(): MessageRead
    {
        return new (static::messageReadModelClass());
    }
}
