<?php

namespace Workbench\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Tmsperera\HeadlessChat\Enums\ConversationType;
use Tmsperera\HeadlessChat\Models\Conversation;

class ConversationFactory extends Factory
{
    protected $model = Conversation::class;

    public function definition(): array
    {
        return [
            //
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
}
