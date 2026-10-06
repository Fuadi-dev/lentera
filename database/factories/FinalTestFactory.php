<?php

namespace Database\Factories;

use App\Models\Chapter;
use Illuminate\Database\Eloquent\Factories\Factory;

class FinalTestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'chapter_id' => Chapter::factory(),
            'title' => 'Tes Akhir Bab',
            'minimum_score' => 70,
            'question_count' => 10,
            'unlocks_next_level' => true,
        ];
    }
}
