<?php

namespace Database\Factories;

use App\Models\AiChatSession;
use App\Models\Student;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiChatSession>
 */
class AiChatSessionFactory extends Factory
{
    protected $model = AiChatSession::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'unit_id' => Unit::factory(),
            'message_count' => 0,
            'is_closed' => false,
            'last_message_at' => null,
        ];
    }
}
