<?php

namespace Database\Factories;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClassesFactory extends Factory
{
    public function definition(): array
    {
        return [
            'subject_id' => Subject::factory(),
            'number' => $this->faker->numberBetween(1, 6),
            'title' => 'Kelas '.$this->faker->numberBetween(1, 6),
            'description' => $this->faker->sentence(),
        ];
    }
}
