<?php

namespace Workbench\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use TMSPerera\HeadlessChat\Enums\ConversationType;
use TMSPerera\HeadlessChat\Models\Conversation;

class ConversationFactory extends Factory
{
    protected $model = Conversation::class;

    public function definition(): array
    {
        return [
            'type' => $this->faker->randomElement(ConversationType::cases()),
        ];
    }

    public function directMessage(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => ConversationType::DIRECT_MESSAGE,
            ];
        });
    }

    public function hasParticipations(ParticipationFactory $participationFactory): static
    {
        return $this->has($participationFactory, 'participations');
    }
}
