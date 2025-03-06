<?php

namespace Tmsperera\HeadlessChat\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageReadEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public object $message,
        public object $reader,
    ) {}
}
