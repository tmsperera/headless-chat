<?php

namespace TMSPerera\HeadlessChat\Config;

use Illuminate\Support\Facades\App;
use TMSPerera\HeadlessChat\Models\Conversation;
use TMSPerera\HeadlessChat\Models\Message;
use TMSPerera\HeadlessChat\Models\Participation;
use TMSPerera\HeadlessChat\Models\ReadReceipt;

class HeadlessChatConfig
{
    public static function conversationInstance(): Conversation
    {
        return App::make(Conversation::class);
    }

    public static function messageInstance(): Message
    {
        return App::make(Message::class);
    }

    public static function participationInstance(): Participation
    {
        return App::make(Participation::class);
    }

    public static function readReceiptInstance(): ReadReceipt
    {
        return App::make(ReadReceipt::class);
    }
}
