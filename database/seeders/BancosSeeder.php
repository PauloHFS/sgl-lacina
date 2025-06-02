<?php

namespace Database\Seeders;

use App\Models\Banco;
use Illuminate\Database\Seeder;

class BancosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bancos = [
            ['codigo' => '001', 'nome' => 'Banco do Brasil'],
            ['codigo' => '033', 'nome' => 'Santander'],
            ['codigo' => '104', 'nome' => 'Caixa Econômica Federal'],
            ['codigo' => '237', 'nome' => 'Bradesco'],
            ['codigo' => '341', 'nome' => 'Itaú Unibanco'],
            ['codigo' => '745', 'nome' => 'Citibank'],
            ['codigo' => '399', 'nome' => 'HSBC'],
            ['codigo' => '756', 'nome' => 'Bancoob (Sicoob)'],
            ['codigo' => '748', 'nome' => 'Sicredi'],
            ['codigo' => '077', 'nome' => 'Banco Inter'],
            ['codigo' => '260', 'nome' => 'Nubank'],
            ['codigo' => '290', 'nome' => 'PagSeguro'],
            ['codigo' => '380', 'nome' => 'PicPay'],
            ['codigo' => '136', 'nome' => 'Unicred'],
            ['codigo' => '323', 'nome' => 'Mercado Pago'],
            ['codigo' => '341', 'nome' => 'Iti (Itaú)'],
            ['codigo' => '197', 'nome' => 'Stone Pagamentos'],
            ['codigo' => '102', 'nome' => 'XP Investimentos'],
            ['codigo' => '129', 'nome' => 'UBS Brasil'],
        ];

        foreach ($bancos as $banco) {
            Banco::firstOrCreate(
                ['codigo' => $banco['codigo']],
                ['nome' => $banco['nome']]
            );
        }
    }
}
