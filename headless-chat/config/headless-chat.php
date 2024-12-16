<?php

use Tmsperera\HeadlessChatForLaravel\Providers\Models\Conversation;
use Tmsperera\HeadlessChatForLaravel\Providers\Models\Message;
use Tmsperera\HeadlessChatForLaravel\Providers\Models\Participation;

return [
    'models' => [
        'conversation' => Conversation::class,
        'participation' => Participation::class,
        'message' => Message::class,
    ]
];
