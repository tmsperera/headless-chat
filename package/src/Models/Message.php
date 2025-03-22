<?php

namespace TMSPerera\HeadlessChat\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use TMSPerera\HeadlessChat\Config\HeadlessChatConfig;
use TMSPerera\HeadlessChat\Contracts\Participant;
use TMSPerera\HeadlessChat\Exceptions\InvalidParticipationException;
use TMSPerera\HeadlessChat\Exceptions\ReadBySenderException;
use TMSPerera\HeadlessChat\HeadlessChat;

/**
 * @property Conversation conversation
 * @property Participation participation
 * @property Collection readReceipts
 * @property string content
 * @property array metadata
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property Carbon deleted_at
 */
class Message extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(
            related: HeadlessChatConfig::conversationModelClass(),
            foreignKey: 'conversation_id',
            ownerKey: $this->getKeyName(),
        );
    }

    /**
     * Sender Participation
     */
    public function participation(): BelongsTo
    {
        return $this->belongsTo(
            related: HeadlessChatConfig::participationModelClass(),
            foreignKey: 'participation_id',
        );
    }

    public function readReceipts(): HasMany
    {
        return $this->hasMany(
            related: HeadlessChatConfig::readReceiptModelClass(),
            foreignKey: 'message_id',
        );
    }

    /**
     * @throws ReadBySenderException
     * @throws InvalidParticipationException
     */
    public function read(Participant $reader): ReadReceipt
    {
        return HeadlessChat::readMessage($this, $reader);
    }
}
