<?php

namespace Database\Factories;

use App\Models\Daily;
use App\Models\User;
use App\Models\UsuarioProjeto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Daily>
 */
class DailyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Daily::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'usuario_id' => User::factory(),
            'usuario_projeto_id' => UsuarioProjeto::factory(),
            'data' => $this->faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'ontem' => $this->faker->paragraph(3),
            'observacoes' => $this->faker->optional()->paragraph(2),
            'hoje' => $this->faker->paragraph(3),
            'carga_horaria' => $this->faker->numberBetween(1, 9),
        ];
    }

    /**
     * Daily para hoje.
     */
    public function hoje(): static
    {
        return $this->state(fn(array $attributes) => [
            'data' => now()->format('Y-m-d'),
        ]);
    }

    /**
     * Daily para ontem.
     */
    public function ontem(): static
    {
        return $this->state(fn(array $attributes) => [
            'data' => now()->subDay()->format('Y-m-d'),
        ]);
    }

    /**
     * Daily sem observações.
     */
    public function semObservacoes(): static
    {
        return $this->state(fn(array $attributes) => [
            'observacoes' => null,
        ]);
    }

    /**
     * Daily com carga horária específica.
     */
    public function cargaHoraria(int $horas): static
    {
        return $this->state(fn(array $attributes) => [
            'carga_horaria' => $horas,
        ]);
    }
}
