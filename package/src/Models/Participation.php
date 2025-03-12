<?php

namespace Tmsperera\HeadlessChat\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Tmsperera\HeadlessChat\Collections\ParticipationCollection;
use Tmsperera\HeadlessChat\Config\HeadlessChatConfig;

class Participation extends Model
{
    use HasFactory;

    protected $guarded = [];

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

    public function newCollection(array $models = []): ParticipationCollection
    {
        return new ParticipationCollection($models);
    }
}
