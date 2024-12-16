<?php

use Tmsperera\HeadlessChatForLaravel\Models\Conversation;
use Tmsperera\HeadlessChatForLaravel\Models\Message;
use Tmsperera\HeadlessChatForLaravel\Models\Participation;

return [
    'models' => [
        'conversation' => Conversation::class,
        'participation' => Participation::class,
        'message' => Message::class,
    ],
];
