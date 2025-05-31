<?php

use App\Models\User;
use App\Models\Banco;
use App\Enums\StatusCadastro;

test('usuário pode acessar página de pós cadastro', function () {
    $user = User::factory()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $bancos = Banco::factory()->count(3)->create();

    $response = $this->actingAs($user)->get('/pos-cadastro');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => 
        $page->component('PosCadastro')
             ->has('bancos', 3)
    );
});

test('usuário pode completar cadastro com dados bancários', function () {
    $user = User::factory()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
        'cpf' => null,
        'banco_id' => null,
    ]);

    $banco = Banco::factory()->create();

    $dadosCompletos = [
        'cpf' => '12345678901',
        'rg' => '123456789',
        'uf_rg' => 'PB',
        'orgao_emissor_rg' => 'SSP',
        'telefone' => '(83) 99999-9999',
        'banco_id' => $banco->id,
        'conta_bancaria' => '123456789',
        'agencia' => '1234',
        'cep' => '58000000',
        'endereco' => 'Rua Teste, 123',
        'numero' => '123',
        'bairro' => 'Centro',
        'cidade' => 'Campina Grande',
        'uf' => 'PB',
    ];

    $response = $this->actingAs($user)->post('/profile/update', $dadosCompletos);

    $response->assertRedirect('/dashboard');
    
    $user->refresh();
    expect($user->cpf)->toBe('12345678901');
    expect($user->banco_id)->toBe($banco->id);
    expect($user->conta_bancaria)->toBe('123456789');
});

test('usuário pode editar perfil', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)->get('/profile');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => 
        $page->component('Profile/Edit')
    );
});

test('usuário pode atualizar dados do perfil', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
        'name' => 'Nome Antigo',
        'linkedin_url' => 'https://linkedin.com/in/antigo',
    ]);

    $dadosAtualizados = [
        'name' => 'Nome Novo',
        'linkedin_url' => 'https://linkedin.com/in/novo',
        'github_url' => 'https://github.com/novo',
        'area_atuacao' => 'Desenvolvimento Web',
        'tecnologias' => 'PHP, Laravel, Vue.js',
    ];

    $response = $this->actingAs($user)->patch('/profile', $dadosAtualizados);

    $response->assertRedirect('/profile');
    
    $user->refresh();
    expect($user->name)->toBe('Nome Novo');
    expect($user->linkedin_url)->toBe('https://linkedin.com/in/novo');
    expect($user->github_url)->toBe('https://github.com/novo');
});

test('usuário pode deletar conta', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)
                     ->delete('/profile', [
                         'password' => 'Ab@12312'
                     ]);

    $response->assertRedirect('/');
    $this->assertGuest();
});

test('usuário não pode deletar conta com senha incorreta', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)
                     ->delete('/profile', [
                         'password' => 'senha-errada'
                     ]);

    $response->assertSessionHasErrors('password');
    $this->assertDatabaseHas('users', ['id' => $user->id]);
});

test('dados obrigatórios são validados no completar cadastro', function () {
    $user = User::factory()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)->post('/profile/update', []);

    $response->assertSessionHasErrors([
        'cpf',
        'rg',
        'uf_rg',
        'orgao_emissor_rg',
        'telefone',
        'banco_id',
        'conta_bancaria',
        'agencia',
    ]);
});

test('CPF deve ser único no completar cadastro', function () {
    $cpfExistente = '12345678901';
    
    User::factory()->create(['cpf' => $cpfExistente]);
    
    $user = User::factory()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
        'cpf' => null,
    ]);

    $banco = Banco::factory()->create();

    $response = $this->actingAs($user)->post('/profile/update', [
        'cpf' => $cpfExistente,
        'rg' => '123456789',
        'uf_rg' => 'PB',
        'orgao_emissor_rg' => 'SSP',
        'telefone' => '(83) 99999-9999',
        'banco_id' => $banco->id,
        'conta_bancaria' => '123456789',
        'agencia' => '1234',
    ]);

    $response->assertSessionHasErrors('cpf');
});
