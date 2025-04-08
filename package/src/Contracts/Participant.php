<?php

namespace TMSPerera\HeadlessChat\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use TMSPerera\HeadlessChat\DataTransferObjects\MessageContentDto;
use TMSPerera\HeadlessChat\Models\Conversation;
use TMSPerera\HeadlessChat\Models\Message;
use TMSPerera\HeadlessChat\Models\Participation;
use TMSPerera\HeadlessChat\Models\ReadReceipt;

interface Participant extends EloquentModel
{
    public function participations(): MorphMany;

    public function conversations(): BelongsToMany;

    public function conversationsWithMetrics(): BelongsToMany;

    public function getUnreadConversationCount(): int;

    public function getParticipationIn(Conversation $conversation): ?Participation;

    public function createDirectMessage(
        Participant $recipient,
        MessageContentDto $messageContentDto,
    ): Message;

    public function createReplyMessage(
        Message $parentMessage,
        MessageContentDto $messageContentDto,
    ): Message;

    public function readMessage(Message $message): ReadReceipt;

    public function deleteSentMessage(Message $message): void;

    public function joinConversation(
        Conversation $conversation,
        array $participationMetadata = [],
    ): Participation;
}
