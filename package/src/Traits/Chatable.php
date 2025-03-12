<?php

namespace Tmsperera\HeadlessChat\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\App;
use Tmsperera\HeadlessChat\Actions\SendDirectMessageAction;
use Tmsperera\HeadlessChat\Collections\ParticipantConversationCollection;
use Tmsperera\HeadlessChat\Config\HeadlessChatConfig;
use Tmsperera\HeadlessChat\Contracts\Participant;
use Tmsperera\HeadlessChat\Models\Message;
use Tmsperera\HeadlessChat\QueryBuilders\ConversationBuilder;

trait Chatable
{
    public function participations(): MorphMany
    {
        return $this->morphMany(HeadlessChatConfig::participationModelClass(), 'participant');
    }

    public function sendDirectMessageTo(Participant $recipient, string $message): Message
    {
        $sendDirectMessage = App::make(SendDirectMessageAction::class);

        return $sendDirectMessage(sender: $this, recipient: $recipient, message: $message);
    }

    public function conversationsQuery(): ConversationBuilder
    {
        return HeadlessChatConfig::conversationModelClass()::query()
            ->whereForParticipant($this);
    }

    public function getConversations(): ParticipantConversationCollection
    {
        $messagesTable = HeadlessChatConfig::newMessageModel()->getTable();

        $conversations = $this->conversationsQuery()
            ->orderByRaw("MAX($messagesTable.created_at) DESC")
            ->get();

        return new ParticipantConversationCollection($conversations);
    }

    public function getUnreadConversationCount(): int
    {
        return $this->conversationsQuery()
            ->having('unread_message_count', '>', 0)
            ->count();
    }
}
