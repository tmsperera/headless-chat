<?php

namespace Tmsperera\HeadlessChat\Traits;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Tmsperera\HeadlessChat\Actions\SendDirectMessageAction;
use Tmsperera\HeadlessChat\Config\HeadlessChatConfig;
use Tmsperera\HeadlessChat\Contracts\Participant;
use Tmsperera\HeadlessChat\Models\Conversation;
use Tmsperera\HeadlessChat\Models\Message;
use Tmsperera\HeadlessChat\Models\Participation;

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

    public function getConversations(): Collection
    {
        return HeadlessChatConfig::conversationModelClass()::query()
            ->whereForParticipant($this)
            ->get();
    }

    public function getUnreadConversations(): Collection
    {
        return HeadlessChatConfig::conversationModelClass()::query()
            ->whereUnreadForParticipant($this)
            ->get();
    }

    public function getUnreadConversationsCount(): int
    {
        return HeadlessChatConfig::conversationModelClass()::query()
            ->whereUnreadForParticipant($this)
            ->count();
    }
}
