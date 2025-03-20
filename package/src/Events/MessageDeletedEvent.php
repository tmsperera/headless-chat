<?php

namespace TMSPerera\HeadlessChat\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use TMSPerera\HeadlessChat\Models\Message;
use TMSPerera\HeadlessChat\Models\Participation;

class MessageDeletedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Message $message,
        public Participation $participation,
    ) {}
}
