<?php

use App\Enums\StatusCadastro;
use App\Models\Baia;
use App\Models\Sala;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
    ]);
    $this->actingAs($this->user);
});

function createSalaWithBaias($salaData, $baiasData)
{
    $sala = Sala::factory()->create($salaData);
    $baias = collect($baiasData)->map(function ($baiaData) use ($sala) {
        return Baia::factory()->create(array_merge($baiaData, ['sala_id' => $sala->id]));
    });

    return [$sala, $baias];
}

function assertSalaAndBaiasUpdated($sala, $expectedSalaData, $baias, $expectedBaiasData, $deletedBaiaIds)
{
    $sala->refresh();
    expect($sala->nome)->toBe($expectedSalaData['nome']);
    expect($sala->descricao)->toBe($expectedSalaData['descricao']);

    foreach ($baias as $index => $baia) {
        $baia->refresh();
        expect($baia->nome)->toBe($expectedBaiasData[$index]['nome']);
        expect($baia->descricao)->toBe($expectedBaiasData[$index]['descricao']);
        expect($baia->ativa)->toBe($expectedBaiasData[$index]['ativa']);
    }

    foreach ($deletedBaiaIds as $deletedBaiaId) {
        expect(Baia::find($deletedBaiaId))->toBeNull();
    }

    expect($sala->baias()->count())->toBe(count($baias));
}

test('pode atualizar sala e deletar baias', function () {
    // Criar uma sala com baias
    [$sala, $baias] = createSalaWithBaias(
        ['nome' => 'Sala Teste', 'descricao' => 'Descrição teste', 'ativa' => true],
        [
            ['nome' => 'Baia 1', 'ativa' => true],
            ['nome' => 'Baia 2', 'ativa' => true],
            ['nome' => 'Baia 3', 'ativa' => true],
        ]
    );
    // Dados para atualização - manter baias 1 e 2, deletar baia 3
    $dadosUpdate = [
        'nome' => 'Sala Teste Atualizada',
        'descricao' => 'Descrição atualizada',
        'ativa' => true,
        'baias' => [
            [
                'id' => $baia1->id,
                'nome' => 'Baia 1 Atualizada',
                'descricao' => 'Descrição baia 1',
                'ativa' => true,
            ],
            [
                'id' => $baia2->id,
                'nome' => 'Baia 2 Atualizada',
                'descricao' => 'Descrição baia 2',
                'ativa' => false,
            ],
        ],
        'baias_deletadas' => [$baia3->id],
    ];

    // Fazer a requisição de atualização
    $response = $this->patch(route('salas.update', $sala->id), $dadosUpdate);

    // Verificar que a resposta foi bem-sucedida
    $response->assertRedirect(route('salas.show', $sala->id));
    $response->assertSessionHas('success', 'Sala atualizada com sucesso!');

    // Verificar que a sala foi atualizada
    $sala->refresh();
    expect($sala->nome)->toBe('Sala Teste Atualizada');
    expect($sala->descricao)->toBe('Descrição atualizada');

    // Verificar que as baias foram atualizadas
    $baia1->refresh();
    $baia2->refresh();
    expect($baia1->nome)->toBe('Baia 1 Atualizada');
    expect($baia1->descricao)->toBe('Descrição baia 1');
    expect($baia2->nome)->toBe('Baia 2 Atualizada');
    expect($baia2->ativa)->toBeFalse();

    // Verificar que a baia 3 foi deletada
    expect(Baia::find($baia3->id))->toBeNull();

    // Verificar que a sala tem apenas 2 baias
    expect($sala->baias()->count())->toBe(2);
});

test('pode criar novas baias ao atualizar sala', function () {
    $sala = Sala::factory()->create([
        'nome' => 'Sala Teste',
        'ativa' => true,
    ]);

    $dadosUpdate = [
        'nome' => 'Sala Teste',
        'ativa' => true,
        'baias' => [
            [
                'nome' => 'Nova Baia 1',
                'descricao' => 'Descrição nova baia 1',
                'ativa' => true,
            ],
            [
                'nome' => 'Nova Baia 2',
                'descricao' => 'Descrição nova baia 2',
                'ativa' => false,
            ],
        ],
        'baias_deletadas' => [],
    ];

    $response = $this->patch(route('salas.update', $sala->id), $dadosUpdate);

    $response->assertRedirect(route('salas.show', $sala->id));

    // Verificar que as novas baias foram criadas
    expect($sala->baias()->count())->toBe(2);

    $baias = $sala->baias()->orderBy('nome')->get();
    expect($baias[0]->nome)->toBe('Nova Baia 1');
    expect($baias[0]->ativa)->toBeTrue();
    expect($baias[1]->nome)->toBe('Nova Baia 2');
    expect($baias[1]->ativa)->toBeFalse();
});

test('validação falha com dados inválidos', function () {
    $sala = Sala::factory()->create();

    $dadosInvalidos = [
        'nome' => '', // Nome obrigatório
        'baias' => [
            [
                'nome' => '', // Nome da baia obrigatório
            ],
        ],
    ];

    $response = $this->patch(route('salas.update', $sala->id), $dadosInvalidos);

    $response->assertSessionHasErrors(['nome', 'baias.0.nome']);
});
