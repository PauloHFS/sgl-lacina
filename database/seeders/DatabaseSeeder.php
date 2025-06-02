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

        $maxwell = $this->createUser([
            'name' => 'Maxwell Guimarães de Oliveira',
            'email' => 'maxwell@computacao.ufcg.edu.br',
            'cpf' => '12345678901',
            'data_nascimento' => '2000-01-01',
            'telefone' => '83999990001',
            'rg' => '1234567',
            'conta_bancaria' => '12345-6',
        ]);

        $campelo = $this->createUser([
            'name' => 'Campelo',
            'email' => 'campelo@computacao.ufcg.edu.br',
            'cpf' => '98765432100',
            'data_nascimento' => '2000-01-01',
            'telefone' => '83999990002',
            'rg' => '7654321',
            'conta_bancaria' => '54321-0',
        ]);

        $paulo = $this->createUser([
            'name' => 'Paulo Hernane Silva',
            'email' => 'paulo.hernane.silva@ccc.ufcg.edu.br',
            'cpf' => '61562374370',
            'data_nascimento' => '2000-01-01',
            'telefone' => '99984297519',
            'rg' => '1112223',
            'conta_bancaria' => '11111-1',
        ]);

        // Criar projetos
        $projetoTCC = $this->createProject([
            'nome' => 'Sistema de Gerenciamento de Laboratório - LaCInA',
            'descricao' => 'Desenvolvimento de sistema web para gestão de recursos humanos, projetos e colaboradores do Laboratório de Computação Inteligente Aplicada da UFCG.',
            'data_inicio' => '2024-03-01',
            'data_termino' => '2025-08-27',
            'cliente' => 'LaCInA - UFCG',
            'tipo' => TipoProjeto::TCC,
        ]);

        $projetoPDI = $this->createProject([
            'nome' => 'TS ETL - Sistema de Extração e Transformação de Dados',
            'descricao' => 'Desenvolvimento de pipeline ETL para processamento de dados temporais usando TypeScript e tecnologias modernas de Big Data.',
            'data_inicio' => '2024-01-15',
            'data_termino' => '2025-12-31',
            'cliente' => 'CQS',
            'tipo' => TipoProjeto::PDI,
        ]);

        // Criar vínculos de coordenação
        $this->createProjectLink([
            'usuario_id' => $maxwell->id,
            'projeto_id' => $projetoTCC->id,
            'tipo_vinculo' => TipoVinculo::COORDENADOR,
            'funcao' => Funcao::COORDENADOR,
            'carga_horaria_semanal' => 8,
            'data_inicio' => '2024-03-01',
            'status' => StatusVinculoProjeto::APROVADO,
        ]);

        $this->createProjectLink([
            'usuario_id' => $maxwell->id,
            'projeto_id' => $projetoPDI->id,
            'tipo_vinculo' => TipoVinculo::COORDENADOR,
            'funcao' => Funcao::COORDENADOR,
            'carga_horaria_semanal' => 12,
            'data_inicio' => '2024-01-15',
            'status' => StatusVinculoProjeto::APROVADO,
        ]);

        // Criar vínculo de colaboração (TCC)
        $this->createProjectLink([
            'usuario_id' => $paulo->id,
            'projeto_id' => $projetoTCC->id,
            'tipo_vinculo' => TipoVinculo::COLABORADOR,
            'funcao' => Funcao::ALUNO,
            'carga_horaria_semanal' => 20,
            'data_inicio' => '2024-03-01',
            'status' => StatusVinculoProjeto::APROVADO,
        ]);
    }

    private function createUser(array $data, StatusCadastro $status = StatusCadastro::ACEITO): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'email_verified_at' => now(),
            'password' => Hash::make('Ab@12312'),
            'status_cadastro' => $status,
            'cpf' => $data['cpf'],
            'data_nascimento' => $data['data_nascimento'],
            'telefone' => $data['telefone'],
            'rg' => $data['rg'],
            'uf_rg' => $data['uf_rg'] ?? 'PB',
            'orgao_emissor_rg' => $data['orgao_emissor_rg'] ?? 'SSP-PB',
            'conta_bancaria' => $data['conta_bancaria'],
        ]);
    }

    private function createProject(array $data): Projeto
    {
        return Projeto::create([
            'nome' => $data['nome'],
            'descricao' => $data['descricao'],
            'data_inicio' => $data['data_inicio'],
            'data_termino' => $data['data_termino'],
            'cliente' => $data['cliente'],
            'tipo' => $data['tipo'],
        ]);
    }

    private function createProjectLink(array $data): UsuarioProjeto
    {
        return UsuarioProjeto::create([
            'usuario_id' => $data['usuario_id'],
            'projeto_id' => $data['projeto_id'],
            'tipo_vinculo' => $data['tipo_vinculo'],
            'funcao' => $data['funcao'],
            'carga_horaria_semanal' => $data['carga_horaria_semanal'],
            'data_inicio' => $data['data_inicio'],
            'status' => $data['status'],
        ]);
    }
}
