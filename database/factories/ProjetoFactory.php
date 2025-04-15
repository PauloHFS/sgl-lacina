<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

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
            'nome' => $this->faker->word(),
            'descricao' => $this->faker->text(),
            'data_inicio' => $this->faker->date(),
            'data_termino' => $this->faker->date(),
            'cliente' => $this->faker->word(),
            'slack_url' => $this->faker->url(),
            'discord_url' => $this->faker->url(),
            'board_url' => $this->faker->url(),
            'git_url' => $this->faker->url(),
            'tipo' => $this->faker->randomElement(['PDI', 'TCC', 'MESTRADO', 'DOUTORADO', 'SUPORTE']), //TODO tentar usar o ENUM para arrumar isso aqui
        ];
    }
}
