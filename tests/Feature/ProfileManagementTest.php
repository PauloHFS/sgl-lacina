<?php

use App\Enums\StatusCadastro;
use App\Enums\Genero;
use App\Models\User;
use App\Models\Banco;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

test('usuário pode fazer upload de foto de perfil', function () {
    Storage::fake('public');

    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $file = UploadedFile::fake()->image('profile.jpg', 300, 300);

    $response = $this->actingAs($user)
        ->put('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'foto_url' => $file,
        ]);

    $response->assertRedirect('/profile');

    $user->refresh();
    expect($user->foto_url)->not->toBeNull();

    $this->assertTrue(Storage::disk('public')->exists($user->foto_url));
});

test('foto de perfil deve ser uma imagem válida', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $invalidFile = UploadedFile::fake()->create('document.pdf', 1024, 'application/pdf');

    $response = $this->actingAs($user)
        ->put('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'foto_url' => $invalidFile,
        ]);

    $response->assertSessionHasErrors(['foto_url']);
});

test('usuário pode completar perfil com informações bancárias', function () {
    $user = User::factory()->create([
        'status_cadastro' => StatusCadastro::IMCOMPLETO,
        'email_verified_at' => now(),
    ]);

    $banco = Banco::factory()->create();

    $profileData = [
        'name' => 'João Silva',
        'genero' => Genero::MASCULINO,
        'data_nascimento' => '1990-01-01',
        'cpf' => '12345678901',
        'rg' => '123456789',
        'uf_rg' => 'SP',
        'orgao_emissor_rg' => 'SSP',
        'cep' => '12345678',
        'endereco' => 'Rua Teste, 123',
        'numero' => '123',
        'bairro' => 'Centro',
        'cidade' => 'São Paulo',
        'estado' => 'SP',
        'telefone' => '11999999999',
        'banco_id' => $banco->id,
        'conta_bancaria' => '123456789',
        'agencia' => '1234',
        'curriculo_lattes_url' => 'http://lattes.cnpq.br/123456789',
    ];

    $response = $this->actingAs($user)
        ->post('/pos-cadastro', $profileData);

    $response->assertRedirect();

    $user->refresh();
    expect($user->status_cadastro)->toBe(StatusCadastro::PENDENTE);
    expect($user->cpf)->toBe('12345678901');
    expect($user->banco_id)->toBe($banco->id);
    expect($user->conta_bancaria)->toBe('123456789');
});

test('campos obrigatórios do perfil são validados', function () {
    $user = User::factory()->create([
        'status_cadastro' => StatusCadastro::IMCOMPLETO,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)
        ->post('/pos-cadastro', [
            'name' => '', // Campo obrigatório vazio
            'email' => 'invalid-email', // Email inválido
        ]);

    $response->assertSessionHasErrors(['name', 'email']);
});

test('CPF deve ter formato válido', function () {
    $user = User::factory()->create([
        'status_cadastro' => StatusCadastro::IMCOMPLETO,
        'email_verified_at' => now(),
    ]);

    $banco = Banco::factory()->create();

    $profileData = [
        'name' => 'João Silva',
        'email' => 'joao@test.com',
        'cpf' => '123', // CPF inválido
        'banco_id' => $banco->id,
        'curriculo_lattes_url' => 'http://lattes.cnpq.br/123456789',
    ];

    $response = $this->actingAs($user)
        ->post('/pos-cadastro', $profileData);

    $response->assertSessionHasErrors(['cpf']);
});

test('CEP deve ter formato válido', function () {
    $user = User::factory()->create([
        'status_cadastro' => StatusCadastro::IMCOMPLETO,
        'email_verified_at' => now(),
    ]);

    $banco = Banco::factory()->create();

    $profileData = [
        'name' => 'João Silva',
        'email' => 'joao@test.com',
        'cpf' => '12345678901',
        'cep' => '123', // CEP inválido
        'banco_id' => $banco->id,
        'curriculo_lattes_url' => 'http://lattes.cnpq.br/123456789',
    ];

    $response = $this->actingAs($user)
        ->post('/pos-cadastro', $profileData);

    $response->assertSessionHasErrors(['cep']);
});

test('usuário pode atualizar links profissionais', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)
        ->put('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'linkedin_url' => 'https://linkedin.com/in/joaosilva',
            'github_url' => 'https://github.com/joaosilva',
            'website_url' => 'https://joaosilva.dev',
        ]);

    $response->assertRedirect('/profile');

    $user->refresh();
    expect($user->linkedin_url)->toBe('https://linkedin.com/in/joaosilva');
    expect($user->github_url)->toBe('https://github.com/joaosilva');
    expect($user->website_url)->toBe('https://joaosilva.dev');
});

test('URLs devem ter formato válido', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)
        ->put('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'linkedin_url' => 'invalid-url',
            'github_url' => 'also-invalid',
        ]);

    $response->assertSessionHasErrors(['linkedin_url', 'github_url']);
});

test('usuário pode atualizar área de atuação e tecnologias', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)
        ->put('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'area_atuacao' => 'Desenvolvimento Web',
            'tecnologias' => 'PHP, Laravel, React, TypeScript',
        ]);

    $response->assertRedirect('/profile');

    $user->refresh();
    expect($user->area_atuacao)->toBe('Desenvolvimento Web');
    expect($user->tecnologias)->toBe('PHP, Laravel, React, TypeScript');
});

test('campos extras JSONB podem ser atualizados', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $camposExtras = [
        'Matricula' => '2024001234',
        'Chave Dell' => 'DELL123456',
        'Periodo Entrada' => '2024.1',
    ];

    $user->update(['campos_extras' => $camposExtras]);

    $user->refresh();
    expect($user->campos_extras)->toBe($camposExtras);
    expect($user->campos_extras['Matricula'])->toBe('2024001234');
});

test('perfil pode ser visualizado com todos os dados', function () {
    $banco = Banco::factory()->create(['nome' => 'Banco do Brasil']);

    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
        'banco_id' => $banco->id,
        'linkedin_url' => 'https://linkedin.com/in/test',
        'github_url' => 'https://github.com/test',
    ]);

    $response = $this->actingAs($user)
        ->get('/profile');

    $response->assertStatus(200);
    $response->assertInertia(
        fn(Assert $page) => $page
            ->component('Profile/Edit')
            ->has('auth.user')
            ->has('bancos')
            ->where('auth.user.linkedin_url', 'https://linkedin.com/in/test')
            ->where('auth.user.github_url', 'https://github.com/test')
    );
});

test('usuário não pode usar CPF já cadastrado', function () {
    $existingUser = User::factory()->cadastroCompleto()->create([
        'cpf' => '11111111111',
    ]);

    $user = User::factory()->create([
        'status_cadastro' => StatusCadastro::IMCOMPLETO,
        'email_verified_at' => now(),
    ]);

    $banco = Banco::factory()->create();

    $response = $this->actingAs($user)
        ->post('/pos-cadastro', [
            'name' => 'João Silva',
            'email' => 'joao@test.com',
            'cpf' => '11111111111', // CPF já existe
            'banco_id' => $banco->id,
            'curriculo_lattes_url' => 'http://lattes.cnpq.br/123456789',
        ]);

    $response->assertSessionHasErrors(['cpf']);
});

test('usuário não pode usar RG já cadastrado', function () {
    $existingUser = User::factory()->cadastroCompleto()->create([
        'rg' => '123456789',
    ]);

    $user = User::factory()->create([
        'status_cadastro' => StatusCadastro::IMCOMPLETO,
        'email_verified_at' => now(),
    ]);

    $banco = Banco::factory()->create();

    $response = $this->actingAs($user)
        ->post('/pos-cadastro', [
            'name' => 'João Silva',
            'email' => 'joao@test.com',
            'cpf' => '22222222222',
            'rg' => '123456789', // RG já existe
            'banco_id' => $banco->id,
            'curriculo_lattes_url' => 'http://lattes.cnpq.br/123456789',
        ]);

    $response->assertSessionHasErrors(['rg']);
});

test('alteração de email requer nova verificação', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'email' => 'old@test.com',
        'email_verified_at' => now(),
        'status_cadastro' => StatusCadastro::ACEITO,
    ]);

    $response = $this->actingAs($user)
        ->put('/profile', [
            'name' => $user->name,
            'email' => 'new@test.com',
        ]);

    $response->assertRedirect('/profile');

    $user->refresh();
    expect($user->email)->toBe('new@test.com');
    expect($user->email_verified_at)->toBeNull();
});

test('gênero deve ser um valor válido do enum', function () {
    $user = User::factory()->create([
        'status_cadastro' => StatusCadastro::IMCOMPLETO,
        'email_verified_at' => now(),
    ]);

    $banco = Banco::factory()->create();

    $response = $this->actingAs($user)
        ->post('/pos-cadastro', [
            'name' => 'João Silva',
            'email' => 'joao@test.com',
            'genero' => 'INVALIDO', // Gênero inválido
            'cpf' => '12345678901',
            'banco_id' => $banco->id,
            'curriculo_lattes_url' => 'http://lattes.cnpq.br/123456789',
        ]);

    $response->assertSessionHasErrors(['genero']);
});

test('data de nascimento deve ter formato válido', function () {
    $user = User::factory()->create([
        'status_cadastro' => StatusCadastro::IMCOMPLETO,
        'email_verified_at' => now(),
    ]);

    $banco = Banco::factory()->create();

    $response = $this->actingAs($user)
        ->post('/pos-cadastro', [
            'name' => 'João Silva',
            'email' => 'joao@test.com',
            'data_nascimento' => 'invalid-date',
            'cpf' => '12345678901',
            'banco_id' => $banco->id,
            'curriculo_lattes_url' => 'http://lattes.cnpq.br/123456789',
        ]);

    $response->assertSessionHasErrors(['data_nascimento']);
});

test('conta bancária deve ter formato válido', function () {
    $user = User::factory()->create([
        'status_cadastro' => StatusCadastro::IMCOMPLETO,
        'email_verified_at' => now(),
    ]);

    $banco = Banco::factory()->create();

    $response = $this->actingAs($user)
        ->post('/pos-cadastro', [
            'name' => 'João Silva',
            'email' => 'joao@test.com',
            'cpf' => '12345678901',
            'banco_id' => $banco->id,
            'conta_bancaria' => 'invalid@account', // Conta bancária inválida
            'curriculo_lattes_url' => 'http://lattes.cnpq.br/123456789',
        ]);

    $response->assertSessionHasErrors(['conta_bancaria']);
});

test('usuário pode deletar conta', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)
        ->delete('/profile', [
            'password' => 'password',
        ]);

    $response->assertRedirect('/');

    $this->assertGuest();
    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

test('deletar conta requer senha correta', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)
        ->delete('/profile', [
            'password' => 'wrong-password',
        ]);

    $response->assertSessionHasErrors(['password']);
    $this->assertDatabaseHas('users', ['id' => $user->id]);
});
