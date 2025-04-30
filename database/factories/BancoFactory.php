<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str; // Import Str

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Banco>
 */
class BancoFactory extends Factory
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
            'codigo' => $this->faker->unique()->numerify('###'), // Generate unique 3-digit code
            'nome' => $this->faker->company() . ' Bank', // Generate a company name for the bank
            'ispb' => $this->faker->optional()->numerify('########'), // Generate optional 8-digit ISPB
        ];
    }
}
