<?php

namespace TMSPerera\HeadlessChat\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Query\JoinClause;
use TMSPerera\HeadlessChat\Collections\ParticipationCollection;
use TMSPerera\HeadlessChat\Config\HeadlessChatConfig;
use TMSPerera\HeadlessChat\Contracts\Participant;
use TMSPerera\HeadlessChat\Exceptions\InvalidParticipationException;
use TMSPerera\HeadlessChat\Exceptions\MessageAlreadyReadException;
use TMSPerera\HeadlessChat\Exceptions\MessageOwnershipException;
use TMSPerera\HeadlessChat\Exceptions\ParticipationLimitExceededException;
use TMSPerera\HeadlessChat\Exceptions\ReadBySenderException;
use TMSPerera\HeadlessChat\HeadlessChat;
use TMSPerera\HeadlessChat\Models\Conversation;
use TMSPerera\HeadlessChat\Models\Message;
use TMSPerera\HeadlessChat\Models\Participation;
use TMSPerera\HeadlessChat\Models\ReadReceipt;

/**
 * @property ParticipationCollection participations
 * @property Collection conversations
 * @property Collection conversationsWithMetrics
 */
trait Chatable
{
    public function participations(): MorphMany
    {
        return $this->morphMany(
            related: HeadlessChatConfig::participationInstance()::class,
            name: 'participant',
        );
    }

    public function conversations(): BelongsToMany
    {
        $conversation = HeadlessChatConfig::conversationInstance();
        $participation = HeadlessChatConfig::participationInstance();

        return $this
            ->belongsToMany(
                related: $conversation::class,
                table: $participation->getTable(),
                foreignPivotKey: $participation->participant()->getForeignKeyName(),
                relatedPivotKey: $participation->conversation()->getForeignKeyName(),
            )
            ->where($participation->qualifyColumn($participation->participant()->getMorphType()), static::class)
            ->withTimestamps();
    }

    /**
     * To get detailed conversation with more metrics.
     * Results contains aggregated values.
     * Results contains pivot values of participations table.
     */
    public function conversationsWithMetrics(): BelongsToMany
    {
        $conversation = HeadlessChatConfig::conversationInstance();
        $participation = HeadlessChatConfig::participationInstance();
        $message = HeadlessChatConfig::messageInstance();
        $readReceipt = HeadlessChatConfig::readReceiptInstance();

        return $this->conversations()
            ->select($conversation->qualifyColumn('*'))
            ->selectRaw('COUNT('.$message->getQualifiedKeyName().') AS total_message_count')
            ->selectRaw('COUNT('.$readReceipt->getQualifiedKeyName().') AS read_message_count')
            ->selectRaw('COUNT('.$message->getQualifiedKeyName().') - COUNT('.$readReceipt->getQualifiedKeyName().') AS unread_message_count')
            ->selectRaw('MAX('.$message->getQualifiedCreatedAtColumn().') AS latest_message_at')
            ->leftJoin($message->getTable(), function (JoinClause $join) use ($message, $conversation) {
                $join
                    ->on(
                        $message->qualifyColumn($message->conversation()->getForeignKeyName()),
                        '=',
                        $conversation->getQualifiedKeyName(),
                    )
                    ->whereNull($message->getQualifiedDeletedAtColumn());
            })
            ->leftJoin($readReceipt->getTable(), function (JoinClause $join) use ($readReceipt, $message, $participation) {
                $join
                    ->on(
                        $readReceipt->qualifyColumn($readReceipt->message()->getForeignKeyName()),
                        '=',
                        $message->getQualifiedKeyName(),
                    )
                    ->on(
                        $readReceipt->qualifyColumn($readReceipt->participation()->getForeignKeyName()),
                        '=',
                        $participation->getQualifiedKeyName(),
                    );
            })
            ->orderByRaw('MAX('.$message->getQualifiedCreatedAtColumn().') DESC')
            ->groupBy($conversation->getQualifiedKeyName());
    }

    public function getUnreadConversationCount(): int
    {
        return $this->conversationsWithMetrics()
            ->having('unread_message_count', '>', 0)
            ->count();
    }

    public function getParticipationIn(Conversation $conversation): ?Participation
    {
        return $conversation->getParticipationOf($this);
    }

    /**
     * @throws ParticipationLimitExceededException
     * @throws InvalidParticipationException
     */
    public function sendDirectMessage(
        Participant $recipient,
        string $message,
        array $messageMetadata = [],
    ): Message {
        return HeadlessChat::sendDirectMessage(
            sender: $this,
            recipient: $recipient,
            content: $message,
            messageMetadata: $messageMetadata,
        );
    }

    /**
     * @throws ReadBySenderException
     * @throws InvalidParticipationException
     * @throws MessageAlreadyReadException
     */
    public function readMessage(Message $message): ReadReceipt
    {
        return HeadlessChat::readMessage(message: $message, reader: $this);
    }

    /**
     * @throws InvalidParticipationException
     * @throws MessageOwnershipException
     */
    public function deleteSentMessage(Message $message): void
    {
        HeadlessChat::deleteSentMessage(message: $message, participant: $this);
    }

    /**
     * @throws ParticipationLimitExceededException
     */
    public function joinConversation(Conversation $conversation, array $participationMetadata = []): Participation
    {
        return HeadlessChat::joinConversation(
            participant: $this,
            conversation: $conversation,
            participationMetadata: $participationMetadata,
        );
    }

    /**
     * @throws InvalidParticipationException
     */
    public function replyToMessage(
        Message $parentMessage,
        string $content,
        array $messageMetadata = [],
    ): Message {
        return HeadlessChat::replyToMessage(
            parentMessage: $parentMessage,
            sender: $this,
            content: $content,
            messageMetadata: $messageMetadata,
        );
    }
}
