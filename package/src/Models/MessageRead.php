<?php

namespace Tmsperera\HeadlessChat\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tmsperera\HeadlessChat\Config\HeadlessChatConfig;

class MessageRead extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    public function message(): BelongsTo
    {
        return $this->belongsTo(HeadlessChatConfig::messageModelClass());
    }

    /**
     * Reader Participation
     */
    public function participation(): BelongsTo
    {
        return $this->belongsTo(HeadlessChatConfig::participationModelClass());
    }
}
