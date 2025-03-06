<?php

namespace Workbench\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Tmsperera\HeadlessChat\Models\Conversation;
use Tmsperera\HeadlessChat\Models\Message;
use Tmsperera\HeadlessChat\Models\Participation;

class MessageFactory extends Factory
{
    protected $model = Message::class;

    public function definition(): array
    {
        return [
            'content' => $this->faker->realText(),
            'conversation_id' => ConversationFactory::new(),
            'participation_id' => ParticipationFactory::new(),
        ];
    }

    public function forConversation(Conversation $conversation): static
    {
        return $this->for($conversation, 'conversation');
    }

    public function forParticipation(Participation $participation): static
    {
        return $this->for($participation, 'participation');
    }
}
