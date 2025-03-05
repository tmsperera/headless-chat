<?php

namespace Tmsperera\HeadlessChat\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface Participant
{
    public function getKey();

    public function getMorphClass();

    public function participations(): MorphMany;
}
