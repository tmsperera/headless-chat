<?php

namespace TMSPerera\HeadlessChat\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use TMSPerera\HeadlessChat\Collections\ParticipationCollection;
use TMSPerera\HeadlessChat\Config\HeadlessChatConfig;
use TMSPerera\HeadlessChat\Contracts\Participant;

/**
 * @property Conversation conversation
 * @property Collection messages
 * @property Participant participant
 */
class Participation extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function newCollection(array $models = []): ParticipationCollection
    {
        return new ParticipationCollection($models);
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(HeadlessChatConfig::conversationModelClass());
    }

    public function messages(): HasMany
    {
        return $this->hasMany(HeadlessChatConfig::messageModelClass());
    }

    public function participant(): MorphTo
    {
        return $this->morphTo();
    }
}
