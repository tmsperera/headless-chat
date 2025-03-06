<?php

namespace Tmsperera\HeadlessChat\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\App;
use Tmsperera\HeadlessChat\Actions\ReadMessageAction;
use Tmsperera\HeadlessChat\Config\HeadlessChatConfig;
use Tmsperera\HeadlessChat\Contracts\Participant;
use Tmsperera\HeadlessChat\Exceptions\InvalidParticipationException;
use Tmsperera\HeadlessChat\Exceptions\ReadBySenderException;

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

    public function messageReads(): HasMany
    {
        return $this->hasMany(HeadlessChatConfig::messageReadModelClass());
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
