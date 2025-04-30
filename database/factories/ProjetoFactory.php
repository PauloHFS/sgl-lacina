<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Enums\TipoProjeto;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Projeto>
 */
class ProjetoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => Str::uuid(),
            'nome' => $this->faker->sentence(3),
            'descricao' => $this->faker->text(),
            'data_inicio' => $this->faker->date(),
            'data_termino' => $this->faker->optional()->date(),
            'cliente' => $this->faker->company(),
            'slack_url' => $this->faker->optional()->url(),
            'discord_url' => $this->faker->optional()->url(),
            'board_url' => $this->faker->optional()->url(),
            'git_url' => $this->faker->optional()->url(),
            'tipo' => $this->faker->randomElement(TipoProjeto::cases()),
        ];
    }
}
