<?php

namespace TMSPerera\HeadlessChat\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use TMSPerera\HeadlessChat\Config\HeadlessChatConfig;

/**
 * @property Message message
 * @property Participation participation
 * @property Carbon created_at
 * @property Carbon updated_at
 */
class ReadReceipt extends Model
{
    use HasFactory;

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
