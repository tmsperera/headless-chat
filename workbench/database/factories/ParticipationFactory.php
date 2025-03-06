<?php

namespace Workbench\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Tmsperera\HeadlessChat\Models\Conversation;
use Tmsperera\HeadlessChat\Models\Participation;

class ParticipationFactory extends Factory
{
    protected $model = Participation::class;

    public function definition(): array
    {
        return [
            //
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
