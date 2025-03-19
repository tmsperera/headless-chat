<?php

namespace TMSPerera\HeadlessChat\Traits;

use Deprecated;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Query\JoinClause;
use TMSPerera\HeadlessChat\Collections\ParticipantConversationCollection;
use TMSPerera\HeadlessChat\Collections\ParticipationCollection;
use TMSPerera\HeadlessChat\Config\HeadlessChatConfig;
use TMSPerera\HeadlessChat\Contracts\Participant;
use TMSPerera\HeadlessChat\Exceptions\InvalidParticipationException;
use TMSPerera\HeadlessChat\Exceptions\ParticipationLimitExceededException;
use TMSPerera\HeadlessChat\Exceptions\ReadBySenderException;
use TMSPerera\HeadlessChat\HeadlessChat;
use TMSPerera\HeadlessChat\Models\Conversation;
use TMSPerera\HeadlessChat\Models\Message;
use TMSPerera\HeadlessChat\Models\Participation;
use TMSPerera\HeadlessChat\Models\ReadReceipt;
use TMSPerera\HeadlessChat\QueryBuilders\ConversationBuilder;

/**
 * @property ParticipationCollection participations
 * @property Collection conversations
 * @property Collection conversationsWithMetrics
 */
trait Chatable
{
    public function participations(): MorphMany
    {
        return $this->morphMany(HeadlessChatConfig::participationModelClass(), 'participant');
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

    /**
     * To get detailed conversation with more metrics.
     * Results contains aggregated values.
     * Results contains pivot values of participations table.
     */
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
            ->leftJoin($messagesTable, function (JoinClause $join) use ($messagesTable, $conversationsTable) {
                $join->on("$messagesTable.conversation_id", '=', "$conversationsTable.id")
                    ->whereNull("$messagesTable.deleted_at");
            })
            ->leftJoin($readReceiptsTable, function (JoinClause $join) use ($readReceiptsTable, $messagesTable, $participationsTable) {
                $join->on("$readReceiptsTable.message_id", '=', "$messagesTable.id")
                    ->on("$readReceiptsTable.participation_id", '=', "$participationsTable.id");
            })
            ->orderByRaw("MAX($messagesTable.created_at) DESC")
            ->groupBy("$conversationsTable.id");
    }

    public function getUnreadConversationCount(): int
    {
        return $this->conversationsWithMetrics()
            ->having('unread_message_count', '>', 0)
            ->count();
    }

    #[Deprecated]
    public function conversationsQuery(): ConversationBuilder
    {
        return HeadlessChatConfig::conversationModelClass()::query()
            ->whereForParticipant($this);
    }

    #[Deprecated]
    public function getConversations(): ParticipantConversationCollection
    {
        $messagesTable = HeadlessChatConfig::messageModel()->getTable();

        $conversations = $this->conversationsQuery()
            ->orderByRaw("MAX($messagesTable.created_at) DESC")
            ->get();

        return new ParticipantConversationCollection($conversations);
    }

    /**
     * @throws ParticipationLimitExceededException
     */
    public function sendDirectMessage(Participant $recipient, string $message): Message
    {
        return HeadlessChat::sendDirectMessage(sender: $this, recipient: $recipient, content: $message);
    }

    /**
     * @throws ParticipationLimitExceededException
     */
    public function joinConversation(Conversation $conversation): Participation
    {
        return HeadlessChat::joinConversation(participant: $this, conversation: $conversation);
    }

    /**
     * @throws ReadBySenderException
     * @throws InvalidParticipationException
     */
    public function readMessage(Message $message): ReadReceipt
    {
        return HeadlessChat::readMessage(message: $message, reader: $this);
    }
}
