<?php

namespace Workbench\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use TMSPerera\HeadlessChat\Models\Conversation;
use TMSPerera\HeadlessChat\Models\Message;
use TMSPerera\HeadlessChat\Models\Participation;

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

    public function forConversation(Conversation|ConversationFactory $conversation): static
    {
        return $this->for($conversation, 'conversation');
    }

    /**
     * Sender
     */
    public function forParticipation(Participation|ParticipationFactory $participation): static
    {
        return $this->for($participation, 'participation');
    }
}
