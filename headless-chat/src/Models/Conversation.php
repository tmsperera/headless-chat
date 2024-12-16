<?php

namespace Tmsperera\HeadlessChatForLaravel\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Workbench\Database\Factories\ConversationFactory;

class Conversation extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    protected static function newFactory(): ConversationFactory
    {
        return ConversationFactory::new();
    }

    public function participations(): HasMany
    {
        return $this->hasMany(Participation::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}
