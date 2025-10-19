<?php

use App\Enums\StatusVinculoProjeto;
use App\Models\Projeto;
use App\Models\User;
use App\Models\UsuarioProjeto;

test('endpoint projetos ativos inclui carga horária', function () {
    $user = User::factory()->create();
    $projeto = Projeto::factory()->create();

    $usuarioProjeto = UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'status' => StatusVinculoProjeto::APROVADO,
        'carga_horaria' => 20, // 20 horas semanais
        'data_fim' => null,
    ]);

    $response = $this->actingAs($user)
        ->get('/horarios/projetos-ativos');

    $response->assertStatus(200)
        ->assertJson([
            'projetos' => [
                [
                    'id' => $usuarioProjeto->id,
                    'projeto_id' => $projeto->id,
                    'projeto_nome' => $projeto->nome,
                    'carga_horaria' => 20,
                ],
            ],
        ]);
});

test('endpoint projetos ativos filtra apenas projetos aprovados e ativos', function () {
    $user = User::factory()->create();
    $projeto1 = Projeto::factory()->create();
    $projeto2 = Projeto::factory()->create();
    $projeto3 = Projeto::factory()->create();

    // Projeto aprovado e ativo
    $usuarioProjetoAtivo = UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto1->id,
        'status' => StatusVinculoProjeto::APROVADO,
        'carga_horaria' => 20,
        'data_fim' => null,
    ]);

    // Projeto aprovado mas finalizado
    UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto2->id,
        'status' => StatusVinculoProjeto::APROVADO,
        'carga_horaria' => 15,
        'data_fim' => now(),
    ]);

    // Projeto pendente
    UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto3->id,
        'status' => StatusVinculoProjeto::PENDENTE,
        'carga_horaria' => 10,
        'data_fim' => null,
    ]);

    $response = $this->actingAs($user)
        ->get('/horarios/projetos-ativos');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'projetos')
        ->assertJson([
            'projetos' => [
                [
                    'id' => $usuarioProjetoAtivo->id,
                    'projeto_id' => $projeto1->id,
                    'projeto_nome' => $projeto1->nome,
                    'carga_horaria' => 20,
                ],
            ],
        ]);
});
