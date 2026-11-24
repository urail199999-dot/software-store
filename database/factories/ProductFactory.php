<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return ['name' => fake()->words(3, true), 'slug' => fake()->unique()->slug(), 'description' => fake()->paragraph(), 'price' => 19900, 'download_url' => 'https://example.com/software.zip', 'is_active' => true];
    }
}
