<?php

namespace TMSPerera\HeadlessChat\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use TMSPerera\HeadlessChat\Collections\ParticipationCollection;
use TMSPerera\HeadlessChat\Config\HeadlessChatConfig;
use TMSPerera\HeadlessChat\Enums\ConversationType;
use TMSPerera\HeadlessChat\QueryBuilders\ConversationBuilder;

/**
 * @method static ConversationBuilder query()
 *
 * @property ParticipationCollection participations
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
        ];
    }

    public function newEloquentBuilder($query): ConversationBuilder
    {
        return new ConversationBuilder($query);
    }

    public function participations(): HasMany
    {
        return $this->hasMany(HeadlessChatConfig::participationModelClass());
    }

    public function messages(): HasMany
    {
        return $this->hasMany(HeadlessChatConfig::messageModelClass());
    }
}
