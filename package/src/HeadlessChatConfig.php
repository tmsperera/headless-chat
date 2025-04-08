<?php

namespace TMSPerera\HeadlessChat;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use TMSPerera\HeadlessChat\Models\Conversation;
use TMSPerera\HeadlessChat\Models\Message;
use TMSPerera\HeadlessChat\Models\Participation;
use TMSPerera\HeadlessChat\Models\ReadReceipt;

readonly class HeadlessChatConfig
{
    public string $conversationModelClass;

    public string $participationModelClass;

    public string $messageModelClass;

    public string $readReceiptModelClass;

    public function __construct()
    {
        $this->conversationModelClass = Config::get('headless-chat.models.conversation', Conversation::class);
        $this->participationModelClass = Config::get('headless-chat.models.participation', Participation::class);
        $this->messageModelClass = Config::get('headless-chat.models.message', Message::class);
        $this->readReceiptModelClass = Config::get('headless-chat.models.read_receipt', ReadReceipt::class);
    }

    public static function make(): static
    {
        return App::make(self::class);
    }

    public function conversationModel(): Conversation
    {
        return App::make($this->conversationModelClass);
    }

    public function participationModel(): Participation
    {
        return App::make($this->participationModelClass);
    }

    public function messageModel(): Message
    {
        return App::make($this->messageModelClass);
    }

    public function readReceiptModel(): ReadReceipt
    {
        return App::make($this->readReceiptModelClass);
    }
}
