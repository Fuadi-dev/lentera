<?php

namespace Database\Factories;

use App\Models\Chapter;
use Illuminate\Database\Eloquent\Factories\Factory;

class UnitFactory extends Factory
{
    public function definition(): array
    {
        return [
            'chapter_id' => Chapter::factory(),
            'order' => $this->faker->numberBetween(1, 5),
            'type' => $this->faker->randomElement(['text', 'video', 'quiz']),
            'title' => $this->faker->sentence(4),
            'content' => $this->faker->paragraphs(3, true),
            'media_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'duration' => '05:00',
            'passing_score' => 70,
        ];
    }
}
