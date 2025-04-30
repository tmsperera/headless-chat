<?php

use TMSPerera\HeadlessChat\Models\Conversation;
use TMSPerera\HeadlessChat\Models\Message;
use TMSPerera\HeadlessChat\Models\Participation;
use TMSPerera\HeadlessChat\Models\ReadReceipt;

return [
    /*
     * The fully qualified class names of models.
     */
    'models' => [
        'message' => Message::class,
        'conversation' => Conversation::class,
        'participation' => Participation::class,
        'read_receipt' => ReadReceipt::class,
    ],
];
