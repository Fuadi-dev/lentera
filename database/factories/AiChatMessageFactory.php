<?php

namespace Database\Factories;

use App\Models\AiChatMessage;
use App\Models\AiChatSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiChatMessage>
 */
class AiChatMessageFactory extends Factory
{
    protected $model = AiChatMessage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'session_id' => AiChatSession::factory(),
            'role' => AiChatMessage::ROLE_USER,
            'content' => $this->faker->sentence(),
            'source' => AiChatMessage::SOURCE_USER,
            'is_flagged' => false,
        ];
    }
}
