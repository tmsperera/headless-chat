<?php

namespace Tmsperera\HeadlessChat\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Tmsperera\HeadlessChat\Models\Participation;

trait Chatable
{
    public function participations(): MorphMany
    {
        return $this->morphMany(Participation::class, 'participant');
    }
}
