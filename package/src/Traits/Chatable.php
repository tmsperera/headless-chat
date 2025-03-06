<?php

namespace Tmsperera\HeadlessChat\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\App;
use Tmsperera\HeadlessChat\Contracts\Participant;
use Tmsperera\HeadlessChat\Models\Message;
use Tmsperera\HeadlessChat\Models\Participation;
use Tmsperera\HeadlessChat\Usecases\SendDirectMessage;

trait Chatable
{
    public function participations(): MorphMany
    {
        return $this->morphMany(Participation::class, 'participant');
    }

    public function sendDirectMessageTo(Participant $recipient, string $message): Message
    {
        $sendDirectMessage = App::make(SendDirectMessage::class);

        return $sendDirectMessage(sender: $this, recipient: $recipient, message: $message);
    }
}
