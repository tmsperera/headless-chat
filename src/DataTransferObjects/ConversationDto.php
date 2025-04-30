<?php

namespace TMSPerera\HeadlessChat\DataTransferObjects;

use TMSPerera\HeadlessChat\Enums\ConversationType;

readonly class ConversationDto
{
    public function __construct(
        public ConversationType $conversationType,
        public array $metadata = [],
    ) {}
}
