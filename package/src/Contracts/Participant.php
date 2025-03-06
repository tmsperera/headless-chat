<?php

namespace Tmsperera\HeadlessChat\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Tmsperera\HeadlessChat\Models\Message;

interface Participant
{
    public function getKey();

    public function getMorphClass();

    public function participations(): MorphMany;

    public function sendDirectMessageTo(Participant $recipient, string $message): Message;
}
