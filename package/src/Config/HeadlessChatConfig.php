<?php

namespace TMSPerera\HeadlessChat\Config;

use Illuminate\Support\Facades\App;
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
    protected static function conversationModelClass(): string
    {
        return Config::get('headless-chat.models.conversation', Conversation::class);
    }

    /**
     * @return class-string<Participation>
     */
    protected static function participationModelClass(): string
    {
        return Config::get('headless-chat.models.participation', Participation::class);
    }

    /**
     * @return class-string<Message>
     */
    protected static function messageModelClass(): string
    {
        return Config::get('headless-chat.models.message', Message::class);
    }

    /**
     * @return class-string<ReadReceipt>
     */
    protected static function readReceiptModelClass(): string
    {
        return Config::get('headless-chat.models.read_receipt', ReadReceipt::class);
    }

    public static function conversationInstance(): Conversation
    {
        return App::make(static::conversationModelClass());
    }

    public static function participationInstance(): Participation
    {
        return App::make(static::participationModelClass());
    }

    public static function messageInstance(): Message
    {
        return App::make(static::messageModelClass());
    }

    public static function readReceiptInstance(): ReadReceipt
    {
        return App::make(static::readReceiptModelClass());
    }
}
