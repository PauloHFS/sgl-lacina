<?php

use App\Models\User;
use App\Models\Banco;
use App\Enums\StatusCadastro;
use App\Enums\Genero;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Inertia\Testing\AssertableInertia as Assert;

test('usuário pode acessar página de pós cadastro', function () {
    $user = User::factory()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)->get('/pos-cadastro');

    $response->assertStatus(200);
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
        'genero' => Genero::MASCULINO->value,
        'data_nascimento' => '1990-01-01',
        'cpf' => '11144477735',
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
        'estado' => 'PB',
        'curriculo_lattes_url' => 'http://lattes.cnpq.br/123456789',
    ];

    $response = $this->actingAs($user)->post('/pos-cadastro', $dadosCompletos);

    $response->assertRedirect(route('waiting-approval'));

    $user->refresh();
    expect($user->cpf)->toBe('11144477735');
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
    $response->assertInertia(
        fn($page) =>
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
        'status_cadastro' => StatusCadastro::IMCOMPLETO,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)->post('/pos-cadastro', []);

    $response->assertSessionHasErrors([
        'genero',
        'data_nascimento',
        'cpf',
        'rg',
        'uf_rg',
        'orgao_emissor_rg',
        'telefone',
        'banco_id',
        'conta_bancaria',
        'agencia',
        'cep',
        'endereco',
        'numero',
        'bairro',
        'cidade',
        'estado',
        'curriculo_lattes_url',
    ]);
});

test('CPF deve ser único no completar cadastro', function () {
    $cpfExistente = '12345678901';

    User::factory()->create(['cpf' => $cpfExistente]);

    $user = User::factory()->create([
        'status_cadastro' => StatusCadastro::IMCOMPLETO,
        'email_verified_at' => now(),
        'cpf' => null,
    ]);

    $banco = Banco::factory()->create();

    $response = $this->actingAs($user)->post('/pos-cadastro', [
        'genero' => Genero::MASCULINO->value,
        'data_nascimento' => '1990-01-01',
        'cpf' => $cpfExistente,
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
        'curriculo_lattes_url' => 'http://lattes.cnpq.br/123456789',
    ]);

    $response->assertSessionHasErrors('cpf');
});

test('usuário pode fazer upload de foto de perfil', function () {
    Storage::fake('public');

    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $file = UploadedFile::fake()->image('profile.jpg', 300, 300);

    $response = $this->actingAs($user)
        ->patch('/profile', [
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
        ->patch('/profile', [
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
        'genero' => Genero::MASCULINO->value,
        'data_nascimento' => '1990-01-01',
        'cpf' => '11144477735',
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
    expect($user->cpf)->toBe('11144477735');
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
            'cpf' => '', // Campo obrigatório vazio
            'rg' => '', // Campo obrigatório vazio
            'genero' => '', // Campo obrigatório vazio
        ]);

    $response->assertSessionHasErrors(['cpf', 'rg', 'genero']);
});

test('CPF deve ter formato válido', function () {
    $user = User::factory()->create([
        'status_cadastro' => StatusCadastro::IMCOMPLETO,
        'email_verified_at' => now(),
    ]);

    $banco = Banco::factory()->create();

    $profileData = [
        'genero' => Genero::MASCULINO->value,
        'data_nascimento' => '1990-01-01',
        'cpf' => '123', // CPF inválido
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
        // Dados pessoais
        'genero' => Genero::MASCULINO->value,
        'data_nascimento' => '1990-01-01',
        // Documentos
        'cpf' => '12345678901',
        'rg' => '123456789',
        'uf_rg' => 'PB',
        'orgao_emissor_rg' => 'SSP',
        // Endereço
        'cep' => '123', // CEP inválido
        'endereco' => 'Rua Teste',
        'numero' => '123',
        'bairro' => 'Centro',
        'cidade' => 'Campina Grande',
        'uf' => 'PB',
        // Dados de contato
        'telefone' => '83999999999',
        // Dados bancários
        'banco_id' => $banco->id,
        'conta_bancaria' => '123456789',
        'agencia' => '1234',
        // Dados profissionais
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
        ->patch('/profile', [
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
        ->patch('/profile', [
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
        ->patch('/profile', [
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
    $user = User::factory()->create([
        'status_cadastro' => StatusCadastro::IMCOMPLETO,
        'email_verified_at' => now(),
    ]);

    $banco = Banco::factory()->create();

    $response = $this->actingAs($user)
        ->post('/pos-cadastro', [
            'genero' => Genero::MASCULINO->value,
            'data_nascimento' => '1990-01-01',
            'cpf' => '11111111111', // CPF já existe
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
            'genero' => Genero::MASCULINO->value,
            'data_nascimento' => '1990-01-01',
            'cpf' => '22222222222',
            'rg' => '123456789', // RG já existe
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
        ->patch('/profile', [
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
            // Dados pessoais
            'genero' => 'INVALIDO', // Gênero inválido
            'data_nascimento' => '1990-01-01',
            // Documentos
            'cpf' => '12345678901',
            'rg' => '123456789',
            'uf_rg' => 'PB',
            'orgao_emissor_rg' => 'SSP',
            // Endereço
            'cep' => '58000000',
            'endereco' => 'Rua Teste',
            'numero' => '123',
            'bairro' => 'Centro',
            'cidade' => 'Campina Grande',
            'uf' => 'PB',
            // Dados de contato
            'telefone' => '83999999999',
            // Dados bancários
            'banco_id' => $banco->id,
            'conta_bancaria' => '123456789',
            'agencia' => '1234',
            // Dados profissionais
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
            'genero' => Genero::MASCULINO->value,
            'data_nascimento' => 'invalid-date', // Data inválida
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
            'genero' => Genero::MASCULINO->value,
            'data_nascimento' => '1990-01-01',
            'cpf' => '12345678901',
            'rg' => '123456789',
            'uf_rg' => 'PB',
            'orgao_emissor_rg' => 'SSP',
            'telefone' => '(83) 99999-9999',
            'banco_id' => $banco->id,
            'conta_bancaria' => 'invalid@account', // Conta bancária inválida
            'agencia' => '1234',
            'cep' => '58000000',
            'endereco' => 'Rua Teste, 123',
            'numero' => '123',
            'bairro' => 'Centro',
            'cidade' => 'Campina Grande',
            'uf' => 'PB',
            'curriculo_lattes_url' => 'http://lattes.cnpq.br/123456789',
        ]);

    $response->assertSessionHasErrors(['conta_bancaria']);
});

test('usuário pode deletar conta com senha válida', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)
        ->delete('/profile', [
            'password' => 'Ab@12312',
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

test('validação de tamanho máximo funciona no update do perfil', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)
        ->patch('/profile', [
            'name' => str_repeat('a', 256), // Acima do limite de 255
            'email' => str_repeat('a', 250) . '@teste.com', // Email muito longo
        ]);

    $response->assertSessionHasErrors(['name', 'email']);
});

test('limpeza de dados CPF e CEP funciona no update', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
        'cpf' => '12345678901',
        'cep' => '12345678',
    ]);

    $response = $this->actingAs($user)
        ->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'cpf' => '111.444.777-35', // Com formatação
            'cep' => '58000-000', // Com formatação
        ]);

    $response->assertRedirect('/profile');

    $user->refresh();
    expect($user->cpf)->toBe('11144477735'); // Sem formatação
    expect($user->cep)->toBe('58000000'); // Sem formatação
});

test('campos JSONB (campos_extras) podem ser atualizados via update', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
        'campos_extras' => ['antigo' => 'valor'],
    ]);

    $novosCamposExtras = [
        'Matricula' => '2024001234',
        'Chave Dell' => 'DELL123456',
        'Periodo Entrada' => '2024.1',
        'Observacoes' => 'Colaborador dedicado',
    ];

    // Atualizamos através do método fill, simulando o comportamento do controller
    $user->update(['campos_extras' => $novosCamposExtras]);

    $user->refresh();
    // Verificamos cada campo individualmente em vez de comparar arrays completos
    expect($user->campos_extras['Matricula'])->toBe('2024001234');
    expect($user->campos_extras['Chave Dell'])->toBe('DELL123456');
    expect($user->campos_extras['Periodo Entrada'])->toBe('2024.1');
    expect($user->campos_extras['Observacoes'])->toBe('Colaborador dedicado');
    // Verificamos que o campo antigo foi substituído
    expect($user->campos_extras)->not->toHaveKey('antigo');
});

test('comportamento com dados nulos no update', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
        'linkedin_url' => 'https://linkedin.com/in/old',
        'github_url' => 'https://github.com/old',
        'website_url' => 'https://old.dev',
    ]);

    // Enviando valores vazios (que deveriam ser filtrados pelo array_filter)
    $response = $this->actingAs($user)
        ->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'linkedin_url' => '', // Campo vazio
            'github_url' => null, // Campo null
            'website_url' => '   ', // Campo só com espaços
            'area_atuacao' => 'Nova área', // Campo válido
        ]);

    $response->assertRedirect('/profile');

    $user->refresh();
    // Campos vazios/null não devem sobrescrever valores existentes devido ao array_filter
    expect($user->linkedin_url)->toBe('https://linkedin.com/in/old');
    expect($user->github_url)->toBe('https://github.com/old');
    expect($user->website_url)->toBe('https://old.dev');
    expect($user->area_atuacao)->toBe('Nova área');
});

test('falha no upload de foto mantém dados do usuário', function () {
    Storage::fake('public');

    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
        'foto_url' => 'fotos/foto_antiga.jpg',
    ]);

    // Simular um arquivo muito grande (acima do limite de 5MB)
    $largeFile = UploadedFile::fake()->image('large.jpg')->size(6000); // 6MB

    $response = $this->actingAs($user)
        ->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'foto_url' => $largeFile,
        ]);

    $response->assertSessionHasErrors(['foto_url']);

    $user->refresh();
    // Foto antiga deve ser mantida
    expect($user->foto_url)->toBe('fotos/foto_antiga.jpg');
});

test('atualização de foto substitui foto anterior', function () {
    Storage::fake('public');

    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
        'foto_url' => null,
    ]);

    // Primeira foto
    $primeiraFoto = UploadedFile::fake()->image('primeira.jpg', 300, 300);

    $response = $this->actingAs($user)
        ->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'foto_url' => $primeiraFoto,
        ]);

    $response->assertRedirect('/profile');
    $user->refresh();
    $primeiraFotoPath = $user->foto_url;

    expect($primeiraFotoPath)->not->toBeNull();
    $this->assertTrue(Storage::disk('public')->exists($primeiraFotoPath));

    // Segunda foto (substituindo a primeira)
    $segundaFoto = UploadedFile::fake()->image('segunda.jpg', 400, 400);

    $response = $this->actingAs($user)
        ->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'foto_url' => $segundaFoto,
        ]);

    $response->assertRedirect('/profile');
    $user->refresh();

    expect($user->foto_url)->not->toBe($primeiraFotoPath);
    expect($user->foto_url)->not->toBeNull();
    $this->assertTrue(Storage::disk('public')->exists($user->foto_url));
});

test('update preserva campos não enviados na requisição', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
        'linkedin_url' => 'https://linkedin.com/in/original',
        'github_url' => 'https://github.com/original',
        'area_atuacao' => 'Área Original',
        'tecnologias' => 'Tech Original',
    ]);

    // Atualizando apenas alguns campos
    $response = $this->actingAs($user)
        ->patch('/profile', [
            'name' => 'Nome Atualizado',
            'email' => $user->email,
            'linkedin_url' => 'https://linkedin.com/in/novo',
            // github_url, area_atuacao e tecnologias não enviados
        ]);

    $response->assertRedirect('/profile');

    $user->refresh();
    expect($user->name)->toBe('Nome Atualizado');
    expect($user->linkedin_url)->toBe('https://linkedin.com/in/novo');
    // Campos não enviados devem ser preservados
    expect($user->github_url)->toBe('https://github.com/original');
    expect($user->area_atuacao)->toBe('Área Original');
    expect($user->tecnologias)->toBe('Tech Original');
});

test('validação de formato de email no update', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)
        ->patch('/profile', [
            'name' => $user->name,
            'email' => 'email-inválido', // Email com formato inválido
        ]);

    $response->assertSessionHasErrors(['email']);

    $user->refresh();
    expect($user->email)->not->toBe('email-inválido');
});

test('validação de URLs inválidas no update', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)
        ->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'linkedin_url' => 'url-inválida', // URL inválida
            'github_url' => 'outra-url-inválida', // URL inválida
        ]);

    $response->assertSessionHasErrors(['linkedin_url', 'github_url']);
});

test('dados são limpos corretamente no update (CPF e CEP)', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
        'cpf' => '12345678901',
        'cep' => '12345678',
    ]);

    $response = $this->actingAs($user)
        ->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'cpf' => '111.444.777-35', // CPF com pontos e hífen
            'cep' => '58000-000', // CEP com hífen
        ]);

    $response->assertRedirect('/profile');

    $user->refresh();
    expect($user->cpf)->toBe('11144477735'); // Deve ficar apenas números
    expect($user->cep)->toBe('58000000'); // Deve ficar apenas números
});

test('reproduzir problema upload foto real com dados mistos', function () {
    $user = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $this->actingAs($user);

    // Simular arquivo de imagem real
    Storage::fake('public');
    $file = UploadedFile::fake()->image('nova_foto.jpg', 800, 600)->size(1024); // 1MB

    // Dados típicos enviados do frontend - cenário real
    $formData = [
        'name' => 'Nome Atualizado',
        'email' => 'novo@email.com',
        'foto_url' => $file,
        'telefone' => '85999887766',
        'area_atuacao' => 'Desenvolvimento Web',
        'cpf' => '',  // Campo vazio
        'cep' => '',  // Campo vazio
        'genero' => '', // Campo vazio
    ];

    $response = $this->patch(route('profile.update'), $formData);

    $response->assertRedirect(route('profile.edit'));
    $response->assertSessionHas('status', 'Cadastro atualizado com sucesso!');

    // Verificar se os dados foram salvos corretamente
    $user->refresh();

    // Verificar outros campos
    $this->assertEquals('Nome Atualizado', $user->name);
    $this->assertEquals('novo@email.com', $user->email);
    $this->assertEquals('85999887766', $user->telefone);
    $this->assertEquals('Desenvolvimento Web', $user->area_atuacao);

    // Verificar se o arquivo foi armazenado - AQUI ESTÁ O PROBLEMA
    $this->assertNotNull($user->foto_url, 'foto_url não deveria ser null');
    $this->assertStringStartsWith('fotos/', $user->foto_url);
    $this->assertTrue(Storage::disk('public')->exists($user->foto_url));

    // Log para debug
    Log::info('Teste problema upload - foto_url no DB: ' . $user->foto_url);
    Log::info('Teste problema upload - arquivo existe: ' . (Storage::disk('public')->exists($user->foto_url) ? 'SIM' : 'NÃO'));
});

test('problema frontend - dados sem arquivo real enviado', function () {
    $user = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $this->actingAs($user);

    Storage::fake('public');

    // Simulando dados que chegam quando frontend não configura corretamente o upload
    $formData = [
        'name' => 'Nome Atualizado',
        'email' => 'novo@email.com',
        'foto_url' => '', // Frontend pode enviar string vazia ao invés do arquivo
        'telefone' => '85999887766',
        'area_atuacao' => 'Desenvolvimento Web',
    ];

    $response = $this->patch(route('profile.update'), $formData);

    $response->assertRedirect(route('profile.edit'));

    $user->refresh();

    // Os outros campos devem ser atualizados
    expect($user->name)->toBe('Nome Atualizado');
    expect($user->email)->toBe('novo@email.com');
    expect($user->telefone)->toBe('85999887766');

    // A foto não deve ter sido alterada (deveria permanecer null)
    expect($user->foto_url)->toBeNull();
});

test('upload de foto funciona após correção do frontend', function () {
    $user = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $this->actingAs($user);

    Storage::fake('public');
    $file = UploadedFile::fake()->image('foto_corrigida.jpg', 800, 600)->size(1500); // 1.5MB

    // Simulando como o FormData seria enviado após a correção do frontend
    $formData = [
        '_method' => 'PATCH',
        'name' => 'Nome Após Correção',
        'email' => 'corrigido@teste.com',
        'foto_url' => $file,
        'telefone' => '85988776655',
    ];

    // Usando POST com _method PATCH (como o frontend corrigido fará)
    $response = $this->post(route('profile.update'), $formData);

    $response->assertRedirect(route('profile.edit'));
    $response->assertSessionHas('status', 'Cadastro atualizado com sucesso!');

    $user->refresh();

    // Verificar dados textuais
    expect($user->name)->toBe('Nome Após Correção');
    expect($user->email)->toBe('corrigido@teste.com');
    expect($user->telefone)->toBe('85988776655');

    // Verificar upload da foto
    expect($user->foto_url)->not->toBeNull();
    expect($user->foto_url)->toStartWith('fotos/');
    $this->assertTrue(Storage::disk('public')->exists($user->foto_url));

    // Log para documentar o sucesso
    Log::info('Upload corrigido - foto salva em: ' . $user->foto_url);
});

test('integração completa - upload de foto com atualização de perfil', function () {
    $user = User::factory()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'name' => 'Nome Original',
        'email' => 'original@email.com',
        'foto_url' => null,
    ]);
    $this->actingAs($user);

    Storage::fake('public');
    $foto = UploadedFile::fake()->image('foto_perfil.jpg', 400, 400)->size(800); // 800KB

    // Cenário real: usuário atualiza foto e alguns dados do perfil
    $dadosAtualizacao = [
        '_method' => 'PATCH',
        'name' => 'Nome Completo Atualizado',
        'email' => 'email_atualizado@teste.com',
        'foto_url' => $foto,
        'telefone' => '85987654321',
        'area_atuacao' => 'Desenvolvimento Full Stack',
        'tecnologias' => 'Laravel, React, TypeScript',
        // Campos vazios que não devem alterar dados existentes
        'cpf' => '',
        'rg' => '',
        'endereco' => '',
    ];

    $response = $this->post(route('profile.update'), $dadosAtualizacao);

    $response->assertRedirect(route('profile.edit'));
    $response->assertSessionHas('status', 'Cadastro atualizado com sucesso!');

    // Verificar se todas as atualizações foram aplicadas
    $user->refresh();

    expect($user->name)->toBe('Nome Completo Atualizado');
    expect($user->email)->toBe('email_atualizado@teste.com');
    expect($user->telefone)->toBe('85987654321');
    expect($user->area_atuacao)->toBe('Desenvolvimento Full Stack');
    expect($user->tecnologias)->toBe('Laravel, React, TypeScript');

    // Verificar upload da foto
    expect($user->foto_url)->not->toBeNull();
    expect($user->foto_url)->toStartWith('fotos/');
    $this->assertTrue(Storage::disk('public')->exists($user->foto_url));

    // Verificar que campos vazios não alteraram dados existentes (mantiveram null)
    expect($user->cpf)->toBeNull();
    expect($user->rg)->toBeNull();
    expect($user->endereco)->toBeNull();

    // Verificar que email_verified_at foi resetado devido à mudança de email
    expect($user->email_verified_at)->toBeNull();

    Log::info('Teste integração completa - Upload realizado com sucesso');
    Log::info('Arquivo salvo em: ' . $user->foto_url);
});
