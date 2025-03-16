<?php

namespace TMSPerera\HeadlessChat\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\App;
use TMSPerera\HeadlessChat\Actions\ReadMessageAction;
use TMSPerera\HeadlessChat\Config\HeadlessChatConfig;
use TMSPerera\HeadlessChat\Contracts\Participant;
use TMSPerera\HeadlessChat\Exceptions\InvalidParticipationException;
use TMSPerera\HeadlessChat\Exceptions\ReadBySenderException;

class Message extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(HeadlessChatConfig::conversationModelClass());
    }

    /**
     * Sender Participation
     */
    public function participation(): BelongsTo
    {
        return $this->belongsTo(HeadlessChatConfig::participationModelClass());
    }

    public function readReceipts(): HasMany
    {
        return $this->hasMany(HeadlessChatConfig::readReceiptModelClass());
    }

    /**
     * @throws ReadBySenderException
     * @throws InvalidParticipationException
     */
    public function read(Participant $reader): void
    {
        $readMessage = App::make(ReadMessageAction::class);

        $readMessage(message: $this, reader: $reader);
    }
}
