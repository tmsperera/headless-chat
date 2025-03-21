<?php

namespace TMSPerera\HeadlessChat\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use TMSPerera\HeadlessChat\Collections\ParticipationCollection;
use TMSPerera\HeadlessChat\Config\HeadlessChatConfig;
use TMSPerera\HeadlessChat\Contracts\Participant;
use TMSPerera\HeadlessChat\Enums\ConversationType;
use TMSPerera\HeadlessChat\QueryBuilders\ConversationBuilder;

/**
 * @method static ConversationBuilder query()
 *
 * @property ParticipationCollection participations
 * @property Collection messages
 * @property ConversationType type
 * @property array metadata
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property Carbon deleted_at
 */
class Conversation extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

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
            related: HeadlessChatConfig::participationModelClass(),
            foreignKey: 'conversation_id',
        );
    }

    public function messages(): HasMany
    {
        return $this->hasMany(
            related: HeadlessChatConfig::messageModelClass(),
            foreignKey: 'conversation_id',
        );
    }

    public function getParticipationOf(Participant $participant): ?Participation
    {
        $this->loadMissing('participations.participant');

        return $this->participations
            ->first(function (Participation $participation) use ($participant) {
                return $participation->participant->is($participant);
            });
    }
}
