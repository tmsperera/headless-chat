<?php

namespace Workbench\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use TMSPerera\HeadlessChat\Models\Message;
use TMSPerera\HeadlessChat\Models\Participation;
use TMSPerera\HeadlessChat\Models\ReadReceipt;

class ReadReceiptFactory extends Factory
{
    protected $model = ReadReceipt::class;

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
