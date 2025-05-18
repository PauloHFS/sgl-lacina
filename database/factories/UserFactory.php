<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Enums\StatusCadastro;
use App\Enums\Genero;
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
            'id' => Str::uuid(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('Ab@12312'),
            'remember_token' => Str::random(10),
            'status_cadastro' => fake()->randomElement(StatusCadastro::cases()),
            'genero' => fake()->randomElement(Genero::cases()),
            'data_nascimento' => fake()->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
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
        $bancoAleatorioDoDB = Banco::inRandomOrder()->first();

        return $this->state(function (array $attributes) use ($bancoAleatorioDoDB) {
            return [
                'cpf' => fake()->unique()->numerify('###########'),

                'linkedin_url' => 'https://linkedin.com/in/' . fake()->userName(),
                'github_url' => 'https://github.com/' . fake()->userName(),
                'figma_url' => fake()->optional()->url(),
                'foto_url' => fake()->imageUrl(200, 200, 'people', true, 'User'),
                'curriculo' => fake()->optional()->paragraphs(fake()->numberBetween(2, 4), true),

                'area_atuacao' => fake()->jobTitle(),
                'tecnologias' => implode(', ', fake()->randomElements(
                    ['PHP', 'Laravel', 'JavaScript', 'Vue.js', 'React', 'Node.js', 'Python', 'Django', 'SQL', 'Docker', 'AWS', 'Git', 'HTML', 'CSS'],
                    fake()->numberBetween(2, 5)
                )),

                'rg' => fake()->numerify('#########'),
                'uf_rg' => fake()->stateAbbr(),
                'orgao_emissor_rg' => fake()->randomElement(['SSP', 'DETRAN', 'POLICIA FEDERAL', 'MINISTÉRIO DA DEFESA', 'SECRETARIA DE SEGURANCA PUBLICA']),
                'telefone' => fake()->optional()->phoneNumber(),
                'banco_id' => $bancoAleatorioDoDB ? $bancoAleatorioDoDB->id : null,
                'conta_bancaria' => fake()->optional()->numerify('#########'),
                'agencia' => fake()->optional()->numerify('#####'),
                'cep' => fake()->optional()->numerify('########'),
                'endereco' => fake()->optional()->streetAddress(),
                'numero' => fake()->optional()->buildingNumber(),
                'complemento' => fake()->optional()->secondaryAddress(),
                'bairro' => fake()->optional()->word(),
                'cidade' => fake()->optional()->city(),
                'uf' => fake()->optional()->stateAbbr(),
            ];
        });
    }
}
