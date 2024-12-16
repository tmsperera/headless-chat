<?php

namespace Tmsperera\HeadlessChatForLaravel\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Workbench\Database\Factories\ParticipationFactory;

class Participation extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    protected static function newFactory(): ParticipationFactory
    {
        return ParticipationFactory::new();
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function participant(): MorphTo
    {
        return $this->morphTo();
    }
}
