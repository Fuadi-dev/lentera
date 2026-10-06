<?php

namespace Database\Factories;

use App\Models\Classes;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LevelFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'class_id' => Classes::factory(),
            'number' => $this->faker->numberBetween(1, 3),
            'title' => 'Tingkat ' . $this->faker->numberBetween(1, 3),
            'description' => $this->faker->sentence(),
        ];
    }
}
