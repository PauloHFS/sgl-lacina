<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str; // Import Str

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AreaAtuacao>
 */
class AreaAtuacaoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => Str::uuid(), // Generate UUID
            'nome' => $this->faker->unique()->jobTitle(), // Generate a unique job title as area name
        ];
    }
}
