<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DailyReport>
 */
class DailyReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'data' => $this->faker->date(),
            'horas_trabalhadas' => $this->faker->numberBetween(1, 8),
            'o_que_fez_ontem' => $this->faker->sentence(),
            'o_que_vai_fazer_hoje' => $this->faker->sentence(),
            'observacoes' => $this->faker->optional()->sentence(),
            'usuario_id' => \App\Models\User::factory(),
            'projeto_id' => \App\Models\Projeto::factory(),
        ];
    }
}
