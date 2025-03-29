<?php

namespace Database\Seeders;

use App\Models\Colaborador;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        Colaborador::factory()->withUser([
            'name' => 'Paulo Hernane Fontes e Silva',
            'email' => 'paulo.hernane.Silva@ccc.ufcg.edu.br',
        ])->create();
    }
}
