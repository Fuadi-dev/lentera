<?php

namespace Database\Factories;

use App\Models\Parents;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'parent_id' => Parents::factory(),
            'name' => $this->faker->firstName(),
        ];
    }
}
