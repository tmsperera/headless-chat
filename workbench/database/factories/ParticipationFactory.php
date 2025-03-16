<?php

namespace Workbench\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use TMSPerera\HeadlessChat\Models\Conversation;
use TMSPerera\HeadlessChat\Models\Participation;
use Workbench\App\Models\User;

class ParticipationFactory extends Factory
{
    protected $model = Participation::class;

    public function definition(): array
    {
        return [
            'conversation_id' => ConversationFactory::new(),
            'participant_id' => UserFactory::new(),
            'participant_type' => function (array $attributes) {
                return User::query()->find($attributes['participant_id'])->getMorphClass();
            },
        ];
    }

    public function forConversation(Conversation $conversation): static
    {
        return $this->for($conversation, 'conversation');
    }

    public function forParticipant(Model $model): static
    {
        return $this->for($model, 'participant');
    }
}
