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
            'link_slack' => $this->faker->url(),
            'link_discord' => $this->faker->url(),
            'link_board' => $this->faker->url(),
            'tipo' => $this->faker->word(),
            'created_at' => now(),
        ];
    }
}
