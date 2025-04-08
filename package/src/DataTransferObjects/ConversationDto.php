<?php

namespace TMSPerera\HeadlessChat\DataTransferObjects;

use TMSPerera\HeadlessChat\Enums\ConversationType;
use TMSPerera\HeadlessChat\Models\Conversation;
use TMSPerera\HeadlessChat\Models\Message;

readonly class ConversationDto
{
    public function __construct(
        public ConversationType $conversationType,
        public array $metadata = [],
        public ?Conversation $parentConversation = null,
        public ?Message $message = null,
    ) {}
}
