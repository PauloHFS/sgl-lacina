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

                'campos_extras' => [
                    'Matricula' => fake()->numerify('##########'),
                    'Chave Dell' => fake()->numerify('##########'),
                    'Chave Microsoft' => fake()->numerify('##########'),
                ],

                'curriculo_lattes_url' => fake()->numerify('http://lattes.cnpq.br/################'),
                'linkedin_url' => fake()->optional()->passthrough('https://www.linkedin.com/in/' . fake()->userName()),
                'github_url' => fake()->optional()->passthrough('https://github.com/' . fake()->userName()),
                'figma_url' => fake()->optional()->passthrough('https://www.figma.com/@' . fake()->userName()),

                'area_atuacao' => fake()->jobTitle(),
                'tecnologias' => implode(', ', fake()->randomElements(
                    ['PHP', 'Laravel', 'JavaScript', 'Vue.js', 'React', 'Node.js', 'Python', 'Django', 'SQL', 'Docker', 'AWS', 'Git', 'HTML', 'CSS'],
                    fake()->numberBetween(2, 5)
                )),

                'rg' => fake()->numerify('#########'),
                'uf_rg' => fake()->stateAbbr(),
                'orgao_emissor_rg' => fake()->randomElement(['SSP', 'DETRAN', 'POLICIA FEDERAL', 'MINISTÉRIO DA DEFESA', 'SECRETARIA DE SEGURANCA PUBLICA']),
                'telefone' => fake()->phoneNumber(),
                'banco_id' => $bancoAleatorioDoDB ? $bancoAleatorioDoDB->id : null,
                'conta_bancaria' => fake()->numerify('#########'),
                'agencia' => fake()->numerify('#####'),
                'cep' => fake()->numerify('########'),
                'endereco' => fake()->streetAddress(),
                'numero' => fake()->buildingNumber(),
                'complemento' => fake()->optional()->secondaryAddress(),
                'bairro' => fake()->word(),
                'cidade' => fake()->city(),
                'uf' => fake()->stateAbbr(),
            ];
        });
    }
}
