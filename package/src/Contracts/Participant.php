<?php

namespace Tmsperera\HeadlessChat\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Tmsperera\HeadlessChat\Collections\ParticipantConversationCollection;
use Tmsperera\HeadlessChat\Models\Message;
use Tmsperera\HeadlessChat\QueryBuilders\ConversationBuilder;

interface Participant
{
    public function getKey();

    public function getMorphClass();

    public function participations(): MorphMany;

    public function sendDirectMessageTo(Participant $recipient, string $message): Message;

    public function conversationsQuery(): ConversationBuilder;

    public function getConversations(): ParticipantConversationCollection;

    public function getUnreadConversationCount(): int;
}
