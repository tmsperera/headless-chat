<?php

namespace TMSPerera\HeadlessChat\DataTransferObjects;

use TMSPerera\HeadlessChat\Models\Message;
use TMSPerera\HeadlessChat\Models\Participation;

readonly class MessageDto
{
    public function __construct(
        public MessageContentDto $messageContentDto,
        public Participation $senderParticipation,
        public ?Message $parentMessage = null,
    ) {}
}
