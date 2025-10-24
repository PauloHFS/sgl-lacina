<?php

use App\Enums\StatusCadastro;
use App\Models\User;
use App\Models\Sala;
use App\Models\Baia;

test('docente pode editar sala e suas baias', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    // Criar sala com baias
    $sala = Sala::factory()->create([
        'nome' => 'Sala Original',
        'descricao' => 'Descrição original',
        'ativa' => true,
    ]);

    $baia1 = Baia::factory()->create([
        'sala_id' => $sala->id,
        'nome' => 'Baia Original 1',
        'ativa' => true,
    ]);

    $baia2 = Baia::factory()->create([
        'sala_id' => $sala->id,
        'nome' => 'Baia Original 2',
        'ativa' => true,
    ]);

    // Dados para atualização
    $updateData = [
        'nome' => 'Sala Atualizada',
        'descricao' => 'Nova descrição',
        'ativa' => false,
        'baias' => [
            // Atualizar baia existente
            [
                'id' => $baia1->id,
                'nome' => 'Baia Atualizada 1',
                'descricao' => 'Nova descrição baia 1',
                'ativa' => false,
            ],
            // Manter baia existente
            [
                'id' => $baia2->id,
                'nome' => 'Baia Original 2',
                'descricao' => null,
                'ativa' => true,
            ],
            // Criar nova baia
            [
                'nome' => 'Nova Baia 3',
                'descricao' => 'Descrição nova baia',
                'ativa' => true,
            ],
        ],
        'baias_deletadas' => [], // Nenhuma baia para deletar neste teste
    ];

    $response = $this->actingAs($user)
        ->patch(route('salas.update', $sala->id), $updateData);

    $response->assertRedirect(route('salas.show', $sala->id));
    $response->assertSessionHas('success', 'Sala atualizada com sucesso!');

    // Verificar se sala foi atualizada
    $sala->refresh();
    expect($sala->nome)->toBe('Sala Atualizada');
    expect($sala->descricao)->toBe('Nova descrição');
    expect($sala->ativa)->toBeFalse();

    // Verificar se baias foram atualizadas
    $baias = $sala->baias()->orderBy('nome')->get();
    expect($baias)->toHaveCount(3);

    // Baia atualizada
    $baiaAtualizada = $baias->firstWhere('id', $baia1->id);
    expect($baiaAtualizada->nome)->toBe('Baia Atualizada 1');
    expect($baiaAtualizada->descricao)->toBe('Nova descrição baia 1');
    expect($baiaAtualizada->ativa)->toBeFalse();

    // Nova baia criada
    $novaBaia = $baias->firstWhere('nome', 'Nova Baia 3');
    expect($novaBaia)->not->toBeNull();
    expect($novaBaia->descricao)->toBe('Descrição nova baia');
    expect($novaBaia->ativa)->toBeTrue();
});

test('docente pode deletar baias ao editar sala', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    // Criar sala com baias
    $sala = Sala::factory()->create();
    $baia1 = Baia::factory()->create(['sala_id' => $sala->id, 'nome' => 'Baia 1']);
    $baia2 = Baia::factory()->create(['sala_id' => $sala->id, 'nome' => 'Baia 2']);
    $baia3 = Baia::factory()->create(['sala_id' => $sala->id, 'nome' => 'Baia 3']);

    // Dados para atualização - manter apenas baia1 e baia3, deletar baia2
    $updateData = [
        'nome' => $sala->nome,
        'descricao' => $sala->descricao,
        'ativa' => $sala->ativa,
        'baias' => [
            [
                'id' => $baia1->id,
                'nome' => $baia1->nome,
                'ativa' => $baia1->ativa,
            ],
            [
                'id' => $baia3->id,
                'nome' => $baia3->nome,
                'ativa' => $baia3->ativa,
            ],
        ],
        'baias_deletadas' => [$baia2->id],
    ];

    $response = $this->actingAs($user)
        ->patch(route('salas.update', $sala->id), $updateData);

    $response->assertRedirect(route('salas.show', $sala->id));

    // Verificar se baia foi deletada
    expect(Baia::find($baia2->id))->toBeNull();

    // Verificar se outras baias ainda existem
    expect(Baia::find($baia1->id))->not->toBeNull();
    expect(Baia::find($baia3->id))->not->toBeNull();

    // Verificar contagem final
    expect($sala->baias()->count())->toBe(2);
});

test('validação de campos obrigatórios ao editar sala', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $sala = Sala::factory()->create();

    $response = $this->actingAs($user)
        ->patch(route('salas.update', $sala->id), [
            'nome' => '', // Campo obrigatório vazio
            'baias' => [
                [
                    'nome' => '', // Nome da baia obrigatório vazio
                    'ativa' => true,
                ],
            ],
        ]);

    $response->assertSessionHasErrors(['nome', 'baias.0.nome']);
});
