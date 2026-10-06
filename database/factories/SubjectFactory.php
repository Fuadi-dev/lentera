<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SubjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->randomElement(['Matematika', 'Bahasa Indonesia', 'IPA', 'IPS']),
            'description' => $this->faker->sentence(),
            'thumbnail' => '/images/subjects/default.png',
        ];
    }
}
