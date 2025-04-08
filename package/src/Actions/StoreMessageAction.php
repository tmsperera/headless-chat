<?php

namespace TMSPerera\HeadlessChat\Actions;

use TMSPerera\HeadlessChat\DataTransferObjects\MessageDto;
use TMSPerera\HeadlessChat\Models\Message;

class StoreMessageAction
{
    public function handle(MessageDto $messageDto): Message
    {
        return $messageDto->senderParticipation->messages()->create([
            'conversation_id' => $messageDto->senderParticipation->getAttribute($messageDto->senderParticipation->conversation()->getForeignKeyName()),
            'parent_id' => $messageDto->parentMessage?->getKey(),
            'type' => $messageDto->messageContentDto->type,
            'content' => $messageDto->messageContentDto->content,
            'metadata' => $messageDto->messageContentDto->metadata,
        ]);
    }
}
