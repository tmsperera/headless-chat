<?php

namespace Workbench\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Tmsperera\HeadlessChat\Models\MessageRead;

class MessageReadFactory extends Factory
{
    protected $model = MessageRead::class;

    public function definition(): array
    {
        return [
            'message_id' => MessageFactory::new(),
            'participation_id' => ParticipationFactory::new(),
        ];
    }
}
