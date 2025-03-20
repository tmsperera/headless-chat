<?php

namespace TMSPerera\HeadlessChat\Actions;

use TMSPerera\HeadlessChat\Events\MessageDeletedEvent;
use TMSPerera\HeadlessChat\Models\Message;
use TMSPerera\HeadlessChat\Models\Participation;

class DeleteMessageAction
{
    public function __invoke(Message $message, Participation $participation): void
    {
        $message->delete();

        MessageDeletedEvent::dispatch($message, $participation);
    }
}
