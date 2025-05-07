<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Enums\StatusCadastro;
use App\Enums\Genero;
use App\Models\AreaAtuacao;
use App\Models\Banco;

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
            'id' => Str::uuid(), // Add UUID generation
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('Ab@12312'),
            'remember_token' => Str::random(10),
            'status_cadastro' => fake()->randomElement(StatusCadastro::cases()),
            'genero' => fake()->randomElement(Genero::cases()),
            'data_nascimento' => fake()->date(),
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

        $bancoAleatorioDoDB = Banco::inRandomOrder()->firstOrFail();

        return $this->state(fn(array $attributes) => [
            'cpf' => fake()->unique()->numerify('###########'),

            'linkedin_url' => fake()->optional()->url(),
            'github_url' => fake()->optional()->url(),
            'figma_url' => fake()->optional()->url(),
            'foto_url' => 'https://robohash.org/set1/' . Str::random(16) . '.png',
            'curriculo' => fake()->optional()->text(),

            'rg' => fake()->numerify('#########'),
            'uf_rg' => fake()->stateAbbr(),
            'orgao_emissor_rg' => fake()->word(),
            'telefone' => fake()->optional()->phoneNumber(),
            'banco_id' => $bancoAleatorioDoDB->id,
            'conta_bancaria' => fake()->optional()->numerify('#########'),
            'agencia' => fake()->optional()->numerify('#####'),
            'cep' => fake()->optional()->numerify('########'),
            'endereco' => fake()->optional()->streetAddress(),
            'numero' => fake()->optional()->buildingNumber(),
            'complemento' => fake()->optional()->secondaryAddress(),
            'bairro' => fake()->optional()->word(),
            'cidade' => fake()->optional()->city(),
            'uf' => fake()->optional()->stateAbbr(),
            'status_cadastro' => fake()->randomElement(StatusCadastro::cases()),
            'genero' => fake()->randomElement(Genero::cases()),
            'data_nascimento' => fake()->date(),
        ]);
    }
}
