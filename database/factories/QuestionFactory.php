<?php

namespace Database\Factories;

use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class QuestionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'unit_id' => Unit::factory(),
            'type' => 'multiple_choice',
            'question' => $this->faker->sentence().'?',
            'answer' => [],
            'explanation' => $this->faker->sentence(),
            'points' => 10,
        ];
    }
}
