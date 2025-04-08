<?php

namespace TMSPerera\HeadlessChat\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use TMSPerera\HeadlessChat\Collections\ParticipationCollection;
use TMSPerera\HeadlessChat\Contracts\Participant;
use TMSPerera\HeadlessChat\Enums\ConversationType;
use TMSPerera\HeadlessChat\Facades\HeadlessChat;
use TMSPerera\HeadlessChat\QueryBuilders\ConversationBuilder;

/**
 * @method ConversationBuilder newQuery()
 *
 * @property ParticipationCollection $participations
 * @property Collection $messages
 * @property ConversationType $type
 * @property array $metadata
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class Conversation extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    protected $attributes = [
        'metadata' => '[]',
    ];

    protected function casts(): array
    {
        return [
            'type' => ConversationType::class,
            'metadata' => 'array',
        ];
    }

    public function newEloquentBuilder($query): ConversationBuilder
    {
        return new ConversationBuilder($query);
    }

    public function participations(): HasMany
    {
        return $this->hasMany(
            related: HeadlessChat::config()->participationModel()::class,
            foreignKey: 'conversation_id',
        );
    }

    public function messages(): HasMany
    {
        return $this->hasMany(
            related: HeadlessChat::config()->messageModel()::class,
            foreignKey: 'conversation_id',
        );
    }

    public function getParticipationOf(Participant $participant): ?Participation
    {
        $this->loadMissing('participations.participant');

        return $this->participations->whereParticipant($participant)->first();
    }
}
