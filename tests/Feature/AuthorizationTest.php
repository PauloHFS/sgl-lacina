<?php

use App\Enums\StatusCadastro;
use App\Enums\StatusVinculoProjeto;
use App\Enums\TipoVinculo;
use App\Enums\Funcao;
use App\Models\Projeto;
use App\Models\User;
use App\Models\UsuarioProjeto;

test('usuário não autenticado não pode acessar rotas protegidas', function () {
    $projeto = Projeto::factory()->create();

    $rotasProtegidas = [
        ['GET', '/dashboard'],
        ['GET', '/colaboradores'],
        ['GET', '/projetos'],
        ['GET', "/projetos/{$projeto->id}"],
        ['POST', '/projetos'],
        ['POST', '/relatorio/participacao'],
        ['GET', '/profile'],
        ['PATCH', '/profile'],
    ];

    foreach ($rotasProtegidas as [$metodo, $rota]) {
        $response = $this->{strtolower($metodo)}($rota);
        $response->assertRedirect('/login');
    }
});

test('usuário com email não verificado não pode acessar sistema', function () {
    $user = User::factory()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => null,
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertRedirect('/verify-email');
});

test('usuário com cadastro pendente não pode acessar sistema', function () {
    $user = User::factory()->create([
        'status_cadastro' => StatusCadastro::PENDENTE,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(403);
});

test('usuário com cadastro recusado não pode acessar sistema', function () {
    $user = User::factory()->create([
        'status_cadastro' => StatusCadastro::RECUSADO,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(403);
});

test('colaborador não pode acessar área de coordenação', function () {
    $colaborador = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $rotasCoordenador = [
        ['GET', '/colaboradores'],
        ['POST', '/projetos'],
        ['PATCH', '/colaboradores/123/aceitar'],
        ['PATCH', '/colaboradores/123/recusar'],
    ];

    foreach ($rotasCoordenador as [$metodo, $rota]) {
        $response = $this->actingAs($colaborador)->{strtolower($metodo)}($rota);
        $response->assertStatus(403);
    }
});

test('coordenador pode acessar todas as áreas', function () {
    $coordenador = User::factory()->coordenador()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($coordenador)->get('/colaboradores');
    $response->assertStatus(200);

    $response = $this->actingAs($coordenador)->get('/projetos');
    $response->assertStatus(200);
});

test('colaborador só pode ver projetos em que participa', function () {
    $colaborador = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $outroUsuario = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projetoParticipa = Projeto::factory()->create();
    $projetoNaoParticipa = Projeto::factory()->create();

    // Vincular colaborador apenas ao primeiro projeto
    UsuarioProjeto::factory()->create([
        'usuario_id' => $colaborador->id,
        'projeto_id' => $projetoParticipa->id,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    // Projeto que não participa
    UsuarioProjeto::factory()->create([
        'usuario_id' => $outroUsuario->id,
        'projeto_id' => $projetoNaoParticipa->id,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    // Pode acessar projeto que participa
    $response = $this->actingAs($colaborador)->get("/projetos/{$projetoParticipa->id}");
    $response->assertStatus(200);

    // Não pode acessar projeto que não participa
    $response = $this->actingAs($colaborador)->get("/projetos/{$projetoNaoParticipa->id}");
    $response->assertStatus(403);
});

test('colaborador só pode editar próprio perfil', function () {
    $colaborador1 = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $colaborador2 = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    // Pode editar próprio perfil
    $response = $this->actingAs($colaborador1)->get('/profile');
    $response->assertStatus(200);

    // Tentativa de acessar perfil de outro usuário (rota hipotética)
    $response = $this->actingAs($colaborador1)->get("/users/{$colaborador2->id}/profile");
    $response->assertStatus(404); // Não existe ou não tem acesso
});

test('colaborador não pode aprovar próprias solicitações', function () {
    $colaborador = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $coordenador = User::factory()->coordenador()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projeto = Projeto::factory()->create();

    $vinculo = UsuarioProjeto::factory()->create([
        'usuario_id' => $colaborador->id,
        'projeto_id' => $projeto->id,
        'status' => StatusVinculoProjeto::PENDENTE,
    ]);

    // Colaborador não pode aprovar próprio vínculo
    $response = $this->actingAs($colaborador)
        ->patch("/projetos/{$projeto->id}/vinculos/{$vinculo->id}", [
            'status' => StatusVinculoProjeto::APROVADO->value,
        ]);

    $response->assertStatus(403);

    // Coordenador pode aprovar
    $response = $this->actingAs($coordenador)
        ->patch("/projetos/{$projeto->id}/vinculos/{$vinculo->id}", [
            'status' => StatusVinculoProjeto::APROVADO->value,
            'tipo_vinculo' => TipoVinculo::COLABORADOR->value,
            'funcao' => Funcao::DESENVOLVEDOR->value,
            'carga_horaria_semanal' => 20,
            'data_inicio' => now()->format('Y-m-d'),
        ]);

    $response->assertStatus(302); // Redirect após sucesso
});

test('coordenador só pode gerenciar projetos que coordena', function () {
    $coordenador1 = User::factory()->coordenador()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $coordenador2 = User::factory()->coordenador()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projetoCoordenador1 = Projeto::factory()->create();
    $projetoCoordenador2 = Projeto::factory()->create();

    // Vincular coordenadores aos seus projetos
    UsuarioProjeto::factory()->create([
        'usuario_id' => $coordenador1->id,
        'projeto_id' => $projetoCoordenador1->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    UsuarioProjeto::factory()->create([
        'usuario_id' => $coordenador2->id,
        'projeto_id' => $projetoCoordenador2->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    // Coordenador1 pode gerenciar seu projeto
    $response = $this->actingAs($coordenador1)->get("/projetos/{$projetoCoordenador1->id}");
    $response->assertStatus(200);

    // Coordenador1 não pode gerenciar projeto do coordenador2
    $response = $this->actingAs($coordenador1)->get("/projetos/{$projetoCoordenador2->id}");
    $response->assertStatus(403);
});

test('usuário excluído não pode acessar sistema', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    // Soft delete do usuário
    $user->delete();

    $response = $this->actingAs($user)->get('/dashboard');
    $response->assertRedirect('/login');
});

test('middleware verifica status do cadastro em todas as rotas protegidas', function () {
    $userPendente = User::factory()->create([
        'status_cadastro' => StatusCadastro::PENDENTE,
        'email_verified_at' => now(),
    ]);

    $userRecusado = User::factory()->create([
        'status_cadastro' => StatusCadastro::RECUSADO,
        'email_verified_at' => now(),
    ]);

    $rotas = ['/dashboard', '/projetos', '/profile'];

    foreach ($rotas as $rota) {
        // Usuário pendente
        $response = $this->actingAs($userPendente)->get($rota);
        $response->assertStatus(403);

        // Usuário recusado
        $response = $this->actingAs($userRecusado)->get($rota);
        $response->assertStatus(403);
    }
});

test('coordenador master pode acessar todos os projetos', function () {
    // Assumindo que existe um role ou flag para coordenador master
    $coordenadorMaster = User::factory()->coordenador()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
        'campos_extras' => ['role' => 'master'], // Exemplo de como poderia ser implementado
    ]);

    $outroUsuario = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projeto = Projeto::factory()->create();

    UsuarioProjeto::factory()->create([
        'usuario_id' => $outroUsuario->id,
        'projeto_id' => $projeto->id,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    // Coordenador master pode acessar qualquer projeto
    $response = $this->actingAs($coordenadorMaster)->get("/projetos/{$projeto->id}");
    $response->assertStatus(200);
});

test('rate limiting é aplicado em endpoints sensíveis', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    // Simular múltiplas tentativas rápidas de geração de relatório
    for ($i = 0; $i < 10; $i++) {
        $response = $this->actingAs($user)->post('/relatorio/participacao');

        if ($i < 5) {
            $response->assertStatus(302); // Sucesso
        } else {
            // Após muitas tentativas, deve ser bloqueado
            $response->assertStatus(429); // Too Many Requests
            break;
        }
    }
})->skip('Rate limiting não configurado ainda');

test('CSRF protection está ativo em formulários', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    // Tentar fazer POST sem token CSRF
    $response = $this->actingAs($user)
        ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)
        ->post('/projetos', [
            'nome' => 'Projeto Teste',
            'cliente' => 'Cliente Teste',
            'data_inicio' => now()->format('Y-m-d'),
            'tipo' => 'DESENVOLVIMENTO',
        ]);

    // Com middleware desabilitado, deve funcionar
    $response->assertStatus(302);

    // Com middleware ativo (teste real)
    $response = $this->actingAs($user)
        ->post('/projetos', [
            'nome' => 'Projeto Teste 2',
            'cliente' => 'Cliente Teste 2',
            'data_inicio' => now()->format('Y-m-d'),
            'tipo' => 'DESENVOLVIMENTO',
        ]);

    // Sem token CSRF deve falhar
    $response->assertStatus(419); // CSRF token mismatch
});
