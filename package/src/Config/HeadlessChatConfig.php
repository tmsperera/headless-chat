<?php

namespace TMSPerera\HeadlessChat\Config;

use Illuminate\Support\Facades\Config;
use TMSPerera\HeadlessChat\Models\Conversation;
use TMSPerera\HeadlessChat\Models\Message;
use TMSPerera\HeadlessChat\Models\Participation;
use TMSPerera\HeadlessChat\Models\ReadReceipt;

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
     * @return class-string<ReadReceipt>
     */
    public static function readReceiptModelClass(): string
    {
        return Config::get('headless-chat.models.read_receipt');
    }

    public static function conversationModel(): Conversation
    {
        return new (static::conversationModelClass());
    }

    public static function messageModel(): Message
    {
        return new (static::messageModelClass());
    }

    public static function participationModel(): Participation
    {
        return new (static::participationModelClass());
    }

    public static function readReceiptModel(): ReadReceipt
    {
        return new (static::readReceiptModelClass());
    }
}
