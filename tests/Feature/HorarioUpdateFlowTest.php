<?php

use App\Enums\StatusCadastro;
use App\Enums\StatusVinculoProjeto;
use App\Enums\TipoHorario;
use App\Models\Baia;
use App\Models\Horario;
use App\Models\Projeto;
use App\Models\Sala;
use App\Models\User;
use App\Models\UsuarioProjeto;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'status_cadastro' => StatusCadastro::ACEITO
    ]);

    $this->sala = Sala::factory()->create();
    $this->baia = Baia::factory()->create(['sala_id' => $this->sala->id]);

    $this->projeto = Projeto::factory()->create();
    $this->usuarioProjeto = UsuarioProjeto::factory()->create([
        'usuario_id' => $this->user->id,
        'projeto_id' => $this->projeto->id,
        'status' => StatusVinculoProjeto::APROVADO,
        'data_fim' => null
    ]);

    $this->horario = Horario::factory()
        ->paraUsuario($this->user)
        ->create([
            'tipo' => TipoHorario::EM_AULA
        ]);
});

test('deve permitir atualizar horário para trabalho presencial com baia e projeto', function () {
    $response = $this->actingAs($this->user)
        ->patch('/horarios', [
            'horarios' => [
                [
                    'id' => $this->horario->id,
                    'tipo' => TipoHorario::TRABALHO_PRESENCIAL->value,
                    'baia_id' => $this->baia->id,
                    'usuario_projeto_id' => $this->usuarioProjeto->id,
                    'baia_updated_at' => $this->baia->updated_at->toDateTimeString(),
                ]
            ]
        ]);

    $response->assertRedirect('/horarios')
        ->assertSessionHas('success', 'Horários atualizados com sucesso!');

    $this->horario->refresh();
    expect($this->horario->tipo)->toBe(TipoHorario::TRABALHO_PRESENCIAL);
    expect($this->horario->baia_id)->toBe($this->baia->id);
    expect($this->horario->usuario_projeto_id)->toBe($this->usuarioProjeto->id);
});

test('deve permitir atualizar horário para trabalho remoto apenas com projeto', function () {
    $response = $this->actingAs($this->user)
        ->patch('/horarios', [
            'horarios' => [
                [
                    'id' => $this->horario->id,
                    'tipo' => TipoHorario::TRABALHO_REMOTO->value,
                    'usuario_projeto_id' => $this->usuarioProjeto->id,
                ]
            ]
        ]);

    $response->assertRedirect('/horarios')
        ->assertSessionHas('success', 'Horários atualizados com sucesso!');

    $this->horario->refresh();
    expect($this->horario->tipo)->toBe(TipoHorario::TRABALHO_REMOTO);
    expect($this->horario->baia_id)->toBeNull();
    expect($this->horario->usuario_projeto_id)->toBe($this->usuarioProjeto->id);
});

test('deve limpar campos quando mudar para em aula', function () {
    // Primeiro, define um horário de trabalho presencial
    $this->horario->update([
        'tipo' => TipoHorario::TRABALHO_PRESENCIAL,
        'baia_id' => $this->baia->id,
        'usuario_projeto_id' => $this->usuarioProjeto->id
    ]);

    $response = $this->actingAs($this->user)
        ->patch('/horarios', [
            'horarios' => [
                [
                    'id' => $this->horario->id,
                    'tipo' => TipoHorario::EM_AULA->value,
                ]
            ]
        ]);

    $response->assertRedirect('/horarios')
        ->assertSessionHas('success', 'Horários atualizados com sucesso!');

    $this->horario->refresh();
    expect($this->horario->tipo)->toBe(TipoHorario::EM_AULA);
    expect($this->horario->baia_id)->toBeNull();
    expect($this->horario->usuario_projeto_id)->toBeNull();
});

test('deve permitir atualizar apenas o projeto sem especificar tipo', function () {
    // Horário já está como TRABALHO_PRESENCIAL
    $this->horario->update([
        'tipo' => TipoHorario::TRABALHO_PRESENCIAL,
        'baia_id' => $this->baia->id
    ]);

    $response = $this->actingAs($this->user)
        ->patch('/horarios', [
            'horarios' => [
                [
                    'id' => $this->horario->id,
                    'usuario_projeto_id' => $this->usuarioProjeto->id,
                ]
            ]
        ]);

    $response->assertRedirect('/horarios')
        ->assertSessionHas('success', 'Horários atualizados com sucesso!');

    $this->horario->refresh();
    expect($this->horario->tipo)->toBe(TipoHorario::TRABALHO_PRESENCIAL);
    expect($this->horario->baia_id)->toBe($this->baia->id);
    expect($this->horario->usuario_projeto_id)->toBe($this->usuarioProjeto->id);
});

test('deve rejeitar projeto em horário de aula', function () {
    $response = $this->actingAs($this->user)
        ->patch('/horarios', [
            'horarios' => [
                [
                    'id' => $this->horario->id,
                    'tipo' => TipoHorario::EM_AULA->value,
                    'usuario_projeto_id' => $this->usuarioProjeto->id,
                ]
            ]
        ]);

    $response->assertSessionHasErrors(['horarios.0.usuario_projeto_id']);
    expect(session('errors')->get('horarios.0.usuario_projeto_id')[0])
        ->toBe('O projeto só pode ser informado para horários de trabalho presencial ou remoto.');
});

test('endpoint de salas disponíveis deve retornar dados corretos', function () {
    $response = $this->actingAs($this->user)
        ->get('/horarios/salas-disponiveis?dia_da_semana=SEGUNDA&horario=8');

    $response->assertOk()
        ->assertJsonStructure([
            'salas' => [
                '*' => ['id', 'nome', 'baias']
            ]
        ]);
});

test('endpoint de projetos ativos deve retornar dados corretos', function () {
    $response = $this->actingAs($this->user)
        ->get('/horarios/projetos-ativos');

    $response->assertOk()
        ->assertJsonStructure([
            'projetos' => [
                '*' => ['id', 'projeto_id', 'projeto_nome']
            ]
        ]);

    $data = $response->json();
    expect($data['projetos'])->toHaveCount(1);
    expect($data['projetos'][0]['id'])->toBe($this->usuarioProjeto->id);
    expect($data['projetos'][0]['projeto_nome'])->toBe($this->projeto->nome);
});
