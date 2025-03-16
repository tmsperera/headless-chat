<?php

namespace TMSPerera\HeadlessChat\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use TMSPerera\HeadlessChat\Collections\ParticipantConversationCollection;
use TMSPerera\HeadlessChat\Models\Message;
use TMSPerera\HeadlessChat\QueryBuilders\ConversationBuilder;

interface Participant
{
    public function getKey();

    public function getMorphClass();

    public function participations(): MorphMany;

    public function sendDirectMessageTo(Participant $recipient, string $message): Message;

    public function conversations(): BelongsToMany;

    public function conversationsWithMetrics(): BelongsToMany;

    public function getUnreadConversationCount(): int;

    public function conversationsQuery(): ConversationBuilder;

    public function getConversations(): ParticipantConversationCollection;
}
