<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Colaborador>
 */
class ColaboradorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'linkedin' => $this->faker->url(),
            'github' => $this->faker->url(),
            'figma' => $this->faker->url(),
            'foto' => $this->faker->imageUrl(),
            'curriculo' => $this->faker->text(),
            'area_atuacao' => $this->faker->word(),
            'tecnologias' => $this->faker->text(),
            'cpf' => $this->faker->unique()->numerify('###.###.###-##'),
            'rg' => $this->faker->unique()->numerify('##.###.###-#'),
            'uf_rg' => $this->faker->stateAbbr(),
            'conta_bancaria' => $this->faker->bankAccountNumber(),
            'agencia' => $this->faker->bankAccountNumber(),
            'codigo_banco' => $this->faker->bankAccountNumber(),
            'telefone' => $this->faker->phoneNumber(),
            'created_at' => now(),
        ];
    }

    /**
     * Configure o factory para usar um usuário específico existente
     */
    public function forUser(User $user)
    {
        return $this->state(function (array $attributes) use ($user) {
            return [
                'id' => $user->id,
            ];
        });
    }

    /**
     * Configure o factory para criar automaticamente um novo usuário
     */
    public function withUser(array $userAttributes = [])
    {
        return $this->state(function (array $attributes) use ($userAttributes) {
            $user = User::factory()->create($userAttributes);

            return [
                'id' => $user->id,
            ];
        });
    }
}
