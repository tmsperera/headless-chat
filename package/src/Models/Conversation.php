<?php

namespace Tmsperera\HeadlessChat\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tmsperera\HeadlessChat\Collections\ParticipationCollection;
use Tmsperera\HeadlessChat\Config\ConfigModels;
use Tmsperera\HeadlessChat\Enums\ConversationType;
use Tmsperera\HeadlessChat\QueryBuilders\ConversationBuilder;

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

    public function participations(): HasMany
    {
        return $this->hasMany(ConfigModels::participation());
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ConfigModels::message());
    }

    public function newEloquentBuilder($query): ConversationBuilder
    {
        return new ConversationBuilder($query);
    }
}
