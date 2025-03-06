<?php

use Tmsperera\HeadlessChat\Models\Conversation;
use Tmsperera\HeadlessChat\Models\Message;
use Tmsperera\HeadlessChat\Models\MessageRead;
use Tmsperera\HeadlessChat\Models\Participation;

return [
    'models' => [
        'message' => Message::class,
        'conversation' => Conversation::class,
        'participation' => Participation::class,
        'message_read' => MessageRead::class,
    ],
];
