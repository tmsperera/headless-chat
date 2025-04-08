<?php

namespace TMSPerera\HeadlessChat\Traits;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Query\JoinClause;
use TMSPerera\HeadlessChat\Collections\ParticipationCollection;
use TMSPerera\HeadlessChat\Contracts\Participant;
use TMSPerera\HeadlessChat\DataTransferObjects\MessageDto;
use TMSPerera\HeadlessChat\Exceptions\InvalidParticipationException;
use TMSPerera\HeadlessChat\Exceptions\MessageAlreadyReadException;
use TMSPerera\HeadlessChat\Exceptions\MessageOwnershipException;
use TMSPerera\HeadlessChat\Exceptions\ParticipationLimitExceededException;
use TMSPerera\HeadlessChat\Exceptions\ReadBySenderException;
use TMSPerera\HeadlessChat\Facades\HeadlessChat;
use TMSPerera\HeadlessChat\Models\Conversation;
use TMSPerera\HeadlessChat\Models\Message;
use TMSPerera\HeadlessChat\Models\Participation;
use TMSPerera\HeadlessChat\Models\ReadReceipt;

/**
 * @property ParticipationCollection $participations
 * @property Collection $conversations
 * @property Collection $conversationsWithMetrics
 */
trait Chatable
{
    /**
     * @throws Exception
     */
    public static function bootChatable(): void
    {
        if (! new (static::class) instanceof Model) {
            throw new Exception(__TRAIT__.' trait can only be used in '.Model::class.' instance.');
        }
    }

    public function participations(): MorphMany
    {
        return $this->morphMany(
            related: HeadlessChat::config()->participationModel()::class,
            name: 'participant',
        );
    }

    public function conversations(): BelongsToMany
    {
        $conversation = HeadlessChat::config()->conversationModel();
        $participation = HeadlessChat::config()->participationModel();

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
     * To get detailed conversations with more metrics.
     * Results contains aggregated values.
     * Results contains pivot values of participations table.
     */
    public function conversationsWithMetrics(): BelongsToMany
    {
        $conversation = HeadlessChat::config()->conversationModel();
        $participation = HeadlessChat::config()->participationModel();
        $message = HeadlessChat::config()->messageModel();
        $readReceipt = HeadlessChat::config()->readReceiptModel();

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
     * @throws InvalidParticipationException
     * @throws ParticipationLimitExceededException
     */
    public function createDirectMessage(
        Participant $recipient,
        MessageDto $messageDto,
    ): Message {
        return HeadlessChat::createDirectMessage(
            sender: $this,
            recipient: $recipient,
            messageDto: $messageDto,
        );
    }

    /**
     * @throws InvalidParticipationException
     */
    public function createReplyMessage(
        Message $parentMessage,
        MessageDto $messageDto,
    ): Message {
        return HeadlessChat::createMessage(
            conversation: $parentMessage->conversation,
            sender: $this,
            messageDto: $messageDto,
            parentMessage: $parentMessage,
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
        HeadlessChat::deleteSentMessage(message: $message, deleter: $this);
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
}
