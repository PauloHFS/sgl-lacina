<?php

namespace Database\Seeders;

use App\Enums\TipoVinculo;
use App\Enums\Funcao;
use App\Enums\StatusVinculoProjeto;
use App\Enums\TipoProjeto;
use App\Enums\StatusCadastro;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Projeto;
use App\Models\UsuarioProjeto;
use Illuminate\Support\Facades\Hash;
use App\Models\Banco;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application\'s database.
     */
    public function run(): void
    {
        $this->call(ConfiguracaoSistemaSeeder::class);
        $this->call(BancosSeeder::class);
        $this->call(IntervenienteFinanceiroSeeder::class);
    }
}
