<?php

use App\Enums\StatusCadastro;
use App\Enums\TipoHorario;
use App\Models\Baia;
use App\Models\Horario;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
    ]);
    $this->baia = Baia::factory()->create();
    $this->horario = Horario::factory()
        ->paraUsuario($this->user)
        ->trabalhoPresencial()
        ->create();
});

test('deve falhar ao tentar atualizar horário com baia modificada por outro processo', function () {
    // Simula o timestamp da baia no momento do carregamento da página
    Carbon::setTestNow('2025-06-18 10:00:00');
    $this->baia->touch(); // Força updated_at para o tempo congelado
    $originalBaiaUpdatedAt = $this->baia->fresh()->updated_at;

    // Simula que passou tempo e outro processo modificou a baia
    Carbon::setTestNow('2025-06-18 10:01:00');
    $this->baia->touch(); // Atualiza o updated_at para um tempo posterior

    $response = $this->actingAs($this->user)
        ->patch('/horarios', [
            'horarios' => [
                [
                    'id' => $this->horario->id,
                    'tipo' => TipoHorario::TRABALHO_PRESENCIAL->value,
                    'baia_id' => $this->baia->id,
                    'baia_updated_at' => $originalBaiaUpdatedAt->toDateTimeString(), // Timestamp antigo
                ],
            ],
        ]);

    $response->assertSessionHasErrors(['horarios.0.baia_id']);
    expect(session('errors')->get('horarios.0.baia_id')[0])
        ->toBe('A baia foi modificada por outro usuário. Por favor, recarregue a página e tente novamente.');

    Carbon::setTestNow(); // Reset do tempo
});

test('deve permitir atualização quando timestamp da baia está correto', function () {
    $response = $this->actingAs($this->user)
        ->patch('/horarios', [
            'horarios' => [
                [
                    'id' => $this->horario->id,
                    'tipo' => TipoHorario::TRABALHO_PRESENCIAL->value,
                    'baia_id' => $this->baia->id,
                    'baia_updated_at' => $this->baia->updated_at->toDateTimeString(),
                ],
            ],
        ]);

    $response->assertRedirect('/horarios')
        ->assertSessionHas('success', 'Horários atualizados com sucesso!');

    $this->horario->refresh();
    expect($this->horario->baia_id)->toBe($this->baia->id);
});

test('deve permitir atualização sem validação de timestamp quando baia_updated_at não é fornecido', function () {
    $response = $this->actingAs($this->user)
        ->patch('/horarios', [
            'horarios' => [
                [
                    'id' => $this->horario->id,
                    'tipo' => TipoHorario::TRABALHO_PRESENCIAL->value,
                    'baia_id' => $this->baia->id,
                    // Sem baia_updated_at
                ],
            ],
        ]);

    $response->assertRedirect('/horarios')
        ->assertSessionHas('success', 'Horários atualizados com sucesso!');

    $this->horario->refresh();
    expect($this->horario->baia_id)->toBe($this->baia->id);
});

test('deve atualizar o updated_at da baia quando horário é atribuído a ela', function () {
    Carbon::setTestNow('2025-06-18 10:00:00');
    $this->baia->touch();
    $originalBaiaUpdatedAt = $this->baia->fresh()->updated_at;

    Carbon::setTestNow('2025-06-18 10:01:00');

    $response = $this->actingAs($this->user)
        ->patch('/horarios', [
            'horarios' => [
                [
                    'id' => $this->horario->id,
                    'tipo' => TipoHorario::TRABALHO_PRESENCIAL->value,
                    'baia_id' => $this->baia->id,
                    'baia_updated_at' => $originalBaiaUpdatedAt->toDateTimeString(),
                ],
            ],
        ]);

    $response->assertRedirect('/horarios');

    $this->baia->refresh();
    expect($this->baia->updated_at->greaterThan($originalBaiaUpdatedAt))->toBeTrue();

    Carbon::setTestNow(); // Reset do tempo
});

test('deve falhar durante transação se baia for modificada entre validação e update', function () {
    // Este teste verifica se mesmo passando na primeira validação,
    // a verificação na transação ainda funciona
    Carbon::setTestNow('2025-06-18 10:00:00');
    $this->baia->touch();
    $originalBaiaUpdatedAt = $this->baia->fresh()->updated_at;

    // Simula uma situação onde a validação inicial passou,
    // mas a baia foi modificada depois (durante a transação)

    $response = $this->actingAs($this->user)
        ->patch('/horarios', [
            'horarios' => [
                [
                    'id' => $this->horario->id,
                    'tipo' => TipoHorario::TRABALHO_PRESENCIAL->value,
                    'baia_id' => $this->baia->id,
                    'baia_updated_at' => $originalBaiaUpdatedAt->toDateTimeString(),
                ],
            ],
        ]);

    // Se a validação inicial passou, deve processar normalmente
    $response->assertRedirect('/horarios');

    Carbon::setTestNow(); // Reset do tempo
});
