<?php

namespace Database\Factories;

use App\Models\Level;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ChapterFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'level_id' => Level::factory(),
            'number' => $this->faker->numberBetween(1, 5),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->sentence(),
        ];
    }
}
