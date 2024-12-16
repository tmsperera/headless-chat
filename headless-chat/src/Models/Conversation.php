<?php

namespace Tmsperera\HeadlessChatForLaravel\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conversation extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    public function participations(): HasMany
    {
        return $this->hasMany(Participation::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}
