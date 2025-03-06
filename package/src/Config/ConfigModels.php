<?php

namespace Tmsperera\HeadlessChat\Config;

use Illuminate\Support\Facades\Config;
use Tmsperera\HeadlessChat\Models\Conversation;
use Tmsperera\HeadlessChat\Models\Message;
use Tmsperera\HeadlessChat\Models\MessageRead;
use Tmsperera\HeadlessChat\Models\Participation;

class ConfigModels
{
    /**
     * @return class-string<Conversation>
     */
    public static function conversation(): string
    {
        return Config::get('headless-chat.models.conversation');
    }

    /**
     * @return class-string<Message>
     */
    public static function message(): string
    {
        return Config::get('headless-chat.models.message');
    }

    /**
     * @return class-string<Participation>
     */
    public static function participation(): string
    {
        return Config::get('headless-chat.models.participation');
    }

    /**
     * @return class-string<MessageRead>
     */
    public static function messageRead(): string
    {
        return Config::get('headless-chat.models.message_read');
    }
}
