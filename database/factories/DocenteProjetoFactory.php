<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DocenteProjeto>
 */
class DocenteProjetoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
        ];
    }

    public function withDocente($docenteId)
    {
        return $this->state(function (array $attributes) use ($docenteId) {
            return [
                'docente_id' => $docenteId,
            ];
        });
    }

    public function withProjeto($projetoId)
    {
        return $this->state(function (array $attributes) use ($projetoId) {
            return [
                'projeto_id' => $projetoId,
            ];
        });
    }
}
