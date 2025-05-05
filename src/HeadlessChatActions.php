<?php

namespace TMSPerera\HeadlessChat;

use TMSPerera\HeadlessChat\Actions\CreateConversationAction;
use TMSPerera\HeadlessChat\Actions\CreateDirectMessageAction;
use TMSPerera\HeadlessChat\Actions\CreateMessageAction;
use TMSPerera\HeadlessChat\Actions\DeleteMessageAction;
use TMSPerera\HeadlessChat\Actions\DeleteSentMessageAction;
use TMSPerera\HeadlessChat\Actions\JoinConversationAction;
use TMSPerera\HeadlessChat\Actions\ReadMessageAction;
use TMSPerera\HeadlessChat\Actions\StoreMessageAction;

readonly class HeadlessChatActions
{
    public function __construct(
        public CreateConversationAction $createConversationAction,
        public CreateMessageAction $createMessageAction,
        public CreateDirectMessageAction $createDirectMessageAction,
        public StoreMessageAction $storeMessageAction,
        public ReadMessageAction $readMessageAction,
        public JoinConversationAction $joinConversationAction,
        public DeleteMessageAction $deleteMessageAction,
        public DeleteSentMessageAction $deleteSentMessageAction,
    ) {}
}
