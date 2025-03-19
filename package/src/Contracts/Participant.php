<?php

namespace TMSPerera\HeadlessChat\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use TMSPerera\HeadlessChat\Models\Message;

interface Participant
{
    public function getKey();

    public function getMorphClass();

    public function participations(): MorphMany;

    public function sendDirectMessage(Participant $recipient, string $message): Message;

    public function conversations(): BelongsToMany;

    public function conversationsWithMetrics(): BelongsToMany;

    public function getUnreadConversationCount(): int;
}
