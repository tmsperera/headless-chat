<?php

namespace TMSPerera\HeadlessChat\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use TMSPerera\HeadlessChat\Collections\ParticipationCollection;
use TMSPerera\HeadlessChat\Contracts\Participant;
use TMSPerera\HeadlessChat\HeadlessChat;

/**
 * @property Conversation $conversation
 * @property Collection $messages
 * @property Participant $participant
 * @property array $metadata
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Participation extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $attributes = [
        'metadata' => '[]',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function newCollection(array $models = []): ParticipationCollection
    {
        return new ParticipationCollection($models);
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(
            related: HeadlessChat::config()->conversationModel()::class,
            foreignKey: 'conversation_id',
        );
    }

    public function messages(): HasMany
    {
        return $this->hasMany(
            related: HeadlessChat::config()->messageModel()::class,
            foreignKey: 'participation_id',
        );
    }

    public function participant(): MorphTo
    {
        return $this->morphTo();
    }
}
