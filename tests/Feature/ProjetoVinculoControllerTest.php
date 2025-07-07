<?php

use App\Models\User;
use App\Models\Projeto;
use App\Models\UsuarioProjeto;
use App\Enums\StatusCadastro;
use App\Enums\StatusVinculoProjeto;
use App\Enums\TipoVinculo;
use App\Enums\Funcao;
use Carbon\Carbon;

describe('ProjetoVinculoController Update', function () {
    it('permite atualizar campos básicos do vínculo', function () {
        // Criar coordenador
        $coordenador = User::factory()->create([
            'status_cadastro' => StatusCadastro::ACEITO
        ]);
        
        // Criar projeto
        $projeto = Projeto::factory()->create();
        
        // Criar vínculo de coordenador
        $vinculoCoordenador = UsuarioProjeto::factory()->create([
            'usuario_id' => $coordenador->id,
            'projeto_id' => $projeto->id,
            'tipo_vinculo' => TipoVinculo::COORDENADOR,
            'status' => StatusVinculoProjeto::APROVADO,
            'data_inicio' => Carbon::now()->subMonth()
        ]);
        
        // Criar colaborador
        $colaborador = User::factory()->create([
            'status_cadastro' => StatusCadastro::ACEITO
        ]);
        
        // Criar vínculo do colaborador
        $vinculoColaborador = UsuarioProjeto::factory()->create([
            'usuario_id' => $colaborador->id,
            'projeto_id' => $projeto->id,
            'tipo_vinculo' => TipoVinculo::COLABORADOR,
            'status' => StatusVinculoProjeto::APROVADO,
            'carga_horaria' => 20,
            'funcao' => Funcao::DESENVOLVEDOR,
            'data_inicio' => Carbon::now()->subMonth()
        ]);
        
        // Tentar atualizar o vínculo do colaborador
        $response = $this->withoutMiddleware()
            ->actingAs($coordenador)
            ->patch("/vinculo/{$vinculoColaborador->id}", [
                'carga_horaria' => 30,
                'funcao' => Funcao::PESQUISADOR->value,
                'status' => StatusVinculoProjeto::APROVADO->value
            ]);
        
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        // Verificar se os dados foram atualizados
        $this->assertDatabaseHas('usuario_projeto', [
            'id' => $vinculoColaborador->id,
            'carga_horaria' => 30,
            'funcao' => Funcao::PESQUISADOR->value,
            'status' => StatusVinculoProjeto::APROVADO->value
        ]);
    });
    
    it('permite atualizar data_inicio sem afetar data_fim', function () {
        // Criar coordenador
        $coordenador = User::factory()->create([
            'status_cadastro' => StatusCadastro::ACEITO
        ]);
        
        // Criar projeto
        $projeto = Projeto::factory()->create();
        
        // Criar vínculo de coordenador
        $vinculoCoordenador = UsuarioProjeto::factory()->create([
            'usuario_id' => $coordenador->id,
            'projeto_id' => $projeto->id,
            'tipo_vinculo' => TipoVinculo::COORDENADOR,
            'status' => StatusVinculoProjeto::APROVADO,
            'data_inicio' => Carbon::now()->subMonth()
        ]);
        
        // Criar colaborador
        $colaborador = User::factory()->create([
            'status_cadastro' => StatusCadastro::ACEITO
        ]);
        
        $dataInicio = Carbon::now()->subMonth();
        
        // Criar vínculo do colaborador
        $vinculoColaborador = UsuarioProjeto::factory()->create([
            'usuario_id' => $colaborador->id,
            'projeto_id' => $projeto->id,
            'tipo_vinculo' => TipoVinculo::COLABORADOR,
            'status' => StatusVinculoProjeto::APROVADO,
            'carga_horaria' => 20,
            'funcao' => Funcao::DESENVOLVEDOR,
            'data_inicio' => $dataInicio
        ]);
        
        $novaDataInicio = $dataInicio->addDays(5);
        
        // Tentar atualizar apenas a data_inicio
        $response = $this->withoutMiddleware()
            ->actingAs($coordenador)
            ->patch("/vinculo/{$vinculoColaborador->id}", [
                'data_inicio' => $novaDataInicio->format('Y-m-d')
            ]);
        
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        // Verificar se a data_inicio foi atualizada
        $this->assertDatabaseHas('usuario_projeto', [
            'id' => $vinculoColaborador->id,
            'data_inicio' => $novaDataInicio->format('Y-m-d 00:00:00')
        ]);
    });
    
    it('falha ao definir data_fim anterior à data_inicio do banco', function () {
        // Criar coordenador
        $coordenador = User::factory()->create([
            'status_cadastro' => StatusCadastro::ACEITO
        ]);
        
        // Criar projeto
        $projeto = Projeto::factory()->create();
        
        // Criar vínculo de coordenador
        $vinculoCoordenador = UsuarioProjeto::factory()->create([
            'usuario_id' => $coordenador->id,
            'projeto_id' => $projeto->id,
            'tipo_vinculo' => TipoVinculo::COORDENADOR,
            'status' => StatusVinculoProjeto::APROVADO,
            'data_inicio' => Carbon::now()->subMonth()
        ]);
        
        // Criar colaborador
        $colaborador = User::factory()->create([
            'status_cadastro' => StatusCadastro::ACEITO
        ]);
        
        $dataInicio = Carbon::now()->subMonth();
        
        // Criar vínculo do colaborador
        $vinculoColaborador = UsuarioProjeto::factory()->create([
            'usuario_id' => $colaborador->id,
            'projeto_id' => $projeto->id,
            'tipo_vinculo' => TipoVinculo::COLABORADOR,
            'status' => StatusVinculoProjeto::APROVADO,
            'carga_horaria' => 20,
            'funcao' => Funcao::DESENVOLVEDOR,
            'data_inicio' => $dataInicio
        ]);
        
        // Tentar definir data_fim anterior à data_inicio
        $response = $this->withoutMiddleware()
            ->actingAs($coordenador)
            ->patch("/vinculo/{$vinculoColaborador->id}", [
                'data_fim' => $dataInicio->subDays(5)->format('Y-m-d')
            ]);
        
        $response->assertSessionHasErrors(['data_fim']);
    });
});
