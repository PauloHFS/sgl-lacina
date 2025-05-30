<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Banco>
 */
class BancoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $bancos = [
            ['codigo' => '001', 'nome' => 'Banco do Brasil S.A.'],
            ['codigo' => '033', 'nome' => 'Banco Santander (Brasil) S.A.'],
            ['codigo' => '104', 'nome' => 'Caixa Econômica Federal'],
            ['codigo' => '237', 'nome' => 'Banco Bradesco S.A.'],
            ['codigo' => '341', 'nome' => 'Itaú Unibanco S.A.'],
            ['codigo' => '745', 'nome' => 'Banco Citibank S.A.'],
            ['codigo' => '399', 'nome' => 'HSBC Bank Brasil S.A.'],
            ['codigo' => '422', 'nome' => 'Banco Safra S.A.'],
            ['codigo' => '756', 'nome' => 'Banco Cooperativo do Brasil S.A.'],
            ['codigo' => '077', 'nome' => 'Banco Inter S.A.'],
        ];

        $banco = fake()->randomElement($bancos);

        return [
            'codigo' => $banco['codigo'],
            'nome' => $banco['nome'],
        ];
    }
}
