<?php

namespace Workbench\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Tmsperera\HeadlessChat\Models\Message;
use Tmsperera\HeadlessChat\Models\MessageRead;
use Tmsperera\HeadlessChat\Models\Participation;

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

    public function forMessage(Message|MessageFactory $message): static
    {
        return $this->for($message, 'message');
    }

    public function forParticipation(Participation|ParticipationFactory $participation): static
    {
        return $this->for($participation, 'participation');
    }
}
