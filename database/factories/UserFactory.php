<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function cadastroCompleto(): static
    {
        return $this->state(fn(array $attributes) => [
            'linkedin_url' => fake()->url(),
            'github_url' => fake()->url(),
            'figma_url' => fake()->url(),
            'foto_url' => fake()->url(),
            'curriculo' => fake()->text(),
            'area_atuacao' => fake()->text(),
            'tecnologias' => fake()->text(),

            'cpf' => fake()->unique()->numerify('###.###.###-##'),
            'rg' => fake()->unique()->numerify('##.###.###-#'),
            'uf_rg' => fake()->stateAbbr(),
            'orgao_emissor_rg' => fake()->word(),
            'conta_bancaria' => fake()->numerify('#########'),
            'agencia' => fake()->numerify('#####'),
            'statusCadastro' => 'PENDENTE',
        ]);
    }
}
