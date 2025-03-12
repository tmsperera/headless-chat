<?php

namespace Tmsperera\HeadlessChat\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Query\JoinClause;
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

    public function conversations(): BelongsToMany
    {
        $participationsTable = HeadlessChatConfig::participationModel()->getTable();

        return $this
            ->belongsToMany(
                related: HeadlessChatConfig::conversationModelClass(),
                table: $participationsTable,
                foreignPivotKey: 'participant_id',
                relatedPivotKey: 'conversation_id'
            )
            ->where("$participationsTable.participant_type", static::class)
            ->withTimestamps();
    }

    public function conversationsWithMetrics(): BelongsToMany
    {
        $conversationsTable = HeadlessChatConfig::conversationModel()->getTable();
        $participationsTable = HeadlessChatConfig::participationModel()->getTable();
        $messagesTable = HeadlessChatConfig::messageModel()->getTable();
        $readReceiptsTable = HeadlessChatConfig::readReceiptModel()->getTable();

        return $this->conversations()
            ->select("$conversationsTable.*")
            ->selectRaw("COUNT($messagesTable.id) AS total_message_count")
            ->selectRaw("COUNT($readReceiptsTable.id) AS read_message_count")
            ->selectRaw("COUNT($messagesTable.id) - COUNT($readReceiptsTable.id) AS unread_message_count")
            ->selectRaw("MAX($messagesTable.created_at) AS latest_message_at")
            ->leftJoin($messagesTable, "$messagesTable.conversation_id", '=', "$conversationsTable.id")
            ->leftJoin($readReceiptsTable, function (JoinClause $join) use ($readReceiptsTable, $messagesTable, $participationsTable) {
                $join->on("$readReceiptsTable.message_id", '=', "$messagesTable.id")
                    ->on("$readReceiptsTable.participation_id", '=', "$participationsTable.id");
            })
            ->orderByRaw("MAX($messagesTable.created_at) DESC")
            ->groupBy("$conversationsTable.id");
    }

    public function conversationsQuery(): ConversationBuilder
    {
        return HeadlessChatConfig::conversationModelClass()::query()
            ->whereForParticipant($this);
    }

    public function getConversations(): ParticipantConversationCollection
    {
        $messagesTable = HeadlessChatConfig::messageModel()->getTable();

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
