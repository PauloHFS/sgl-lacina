<?php

use App\Models\Banco;
use App\Models\User;

test('banco pode ser criado com código e nome', function () {
    $banco = Banco::factory()->create([
        'codigo' => '001',
        'nome' => 'Banco do Brasil S.A.',
    ]);

    expect($banco->codigo)->toBe('001');
    expect($banco->nome)->toBe('Banco do Brasil S.A.');
    expect($banco->id)->toBeString();
});

test('banco tem relacionamento um-para-muitos com usuários', function () {
    $banco = Banco::factory()->create();

    User::factory()->count(3)->create([
        'banco_id' => $banco->id,
    ]);

    expect($banco->users)->toHaveCount(3);
    expect($banco->users->first())->toBeInstanceOf(User::class);
});

test('banco não tem timestamps', function () {
    $banco = new Banco;

    expect($banco->timestamps)->toBeFalse();
});

test('banco fillable deve incluir código e nome', function () {
    $banco = new Banco;

    expect($banco->getFillable())->toContain('codigo');
    expect($banco->getFillable())->toContain('nome');
});

test('banco pode ser criado com códigos de bancos reais', function () {
    $bancosReais = [
        ['codigo' => '001', 'nome' => 'Banco do Brasil S.A.'],
        ['codigo' => '033', 'nome' => 'Banco Santander (Brasil) S.A.'],
        ['codigo' => '104', 'nome' => 'Caixa Econômica Federal'],
        ['codigo' => '237', 'nome' => 'Banco Bradesco S.A.'],
        ['codigo' => '341', 'nome' => 'Itaú Unibanco S.A.'],
    ];

    foreach ($bancosReais as $bancoData) {
        $banco = Banco::factory()->create($bancoData);

        expect($banco->codigo)->toBe($bancoData['codigo']);
        expect($banco->nome)->toBe($bancoData['nome']);
    }
});

test('banco deve usar UUID como chave primária', function () {
    $banco = Banco::factory()->create();

    expect($banco->getKeyType())->toBe('string');
    expect(strlen($banco->getKey()))->toBe(36); // UUID length
});
