<?php

namespace Database\Factories;

use App\Models\Baia;
use App\Models\Sala;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Baia>
 */
class BaiaFactory extends Factory
{
    protected $model = Baia::class;

    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'nome' => 'Baia ' . $this->faker->unique()->numberBetween(1, 50),
            'descricao' => $this->faker->optional()->sentence(),
            'ativa' => true,
            'sala_id' => Sala::factory(),
        ];
    }

    public function paraSala(Sala $sala): static
    {
        return $this->state(['sala_id' => $sala->id]);
    }

    public function inativa(): static
    {
        return $this->state(['ativa' => false]);
    }
}
