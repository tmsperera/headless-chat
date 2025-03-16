<?php

use TMSPerera\HeadlessChat\Models\Conversation;
use TMSPerera\HeadlessChat\Models\Message;
use TMSPerera\HeadlessChat\Models\Participation;
use TMSPerera\HeadlessChat\Models\ReadReceipt;

return [
    'models' => [
        'message' => Message::class,
        'conversation' => Conversation::class,
        'participation' => Participation::class,
        'read_receipts' => ReadReceipt::class,
    ],
];
