<?php

namespace TMSPerera\HeadlessChat\Actions;

use TMSPerera\HeadlessChat\DataTransferObjects\MessageDto;
use TMSPerera\HeadlessChat\Models\Message;
use TMSPerera\HeadlessChat\Models\Participation;

class StoreMessageAction
{
    public function handle(
        MessageDto $messageDto,
        Participation $senderParticipation,
        ?Message $parentMessage = null,
    ): Message {
        return $senderParticipation->messages()->create([
            'conversation_id' => $senderParticipation->getAttribute($senderParticipation->conversation()->getForeignKeyName()),
            'parent_id' => $parentMessage?->getKey(),
            'type' => $messageDto->type,
            'content' => $messageDto->content,
            'metadata' => $messageDto->metadata,
        ]);
    }
}
