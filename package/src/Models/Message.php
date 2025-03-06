<?php

namespace Tmsperera\HeadlessChat\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tmsperera\HeadlessChat\HeadlessChatConfig;

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
     * Message sender Participation
     */
    public function participation(): BelongsTo
    {
        return $this->belongsTo(HeadlessChatConfig::participationModelClass());
    }

    public function messageReads(): HasMany
    {
        return $this->hasMany(HeadlessChatConfig::messageReadModelClass());
    }
}
