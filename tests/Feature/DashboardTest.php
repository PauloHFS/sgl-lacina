<?php

use App\Enums\Funcao;
use App\Enums\StatusCadastro;
use App\Enums\StatusVinculoProjeto;
use App\Enums\TipoVinculo;
use App\Models\Projeto;
use App\Models\User;
use App\Models\UsuarioProjeto;

test('usuário pode acessar dashboard', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);
    $response->assertInertia(
        fn ($page) => $page->component('Dashboard')
    );
});

test('dashboard exibe projetos disponíveis para colaborador', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projetos = Projeto::factory()->count(3)->create();

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);
    $response->assertInertia(
        fn ($page) => $page->component('Dashboard')
            ->has('projetos', 3)
    );
});

test('dashboard exibe projetos do coordenador', function () {
    $coordenador = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projeto = Projeto::factory()->create();

    UsuarioProjeto::factory()->create([
        'usuario_id' => $coordenador->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    $response = $this->actingAs($coordenador)->get('/dashboard');

    $response->assertStatus(200);
    $response->assertInertia(
        fn ($page) => $page->component('Dashboard')
            ->has('meusProjetos', 1)
    );
});

test('usuário não autenticado é redirecionado para login', function () {
    $response = $this->get('/dashboard');

    $response->assertRedirect('/login');
});

test('usuário não verificado é redirecionado para verificação', function () {
    $user = User::factory()->create([
        'email_verified_at' => null,
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertRedirect('/verify-email');
});

test('usuário com cadastro pendente é redirecionado para aguardar aprovação', function () {
    $user = User::factory()->create([
        'status_cadastro' => StatusCadastro::PENDENTE,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertRedirect('/waiting-approval');
});

test('usuário sem pós cadastro é redirecionado para completar dados', function () {
    $user = User::factory()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
        'cpf' => null, // Indica que não completou o pós cadastro
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertRedirect('/pos-cadastro');
});

test('dashboard carrega dados corretos para colaborador', function () {
    $colaborador = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projeto = Projeto::factory()->create();

    UsuarioProjeto::factory()->create([
        'usuario_id' => $colaborador->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COLABORADOR,
        'funcao' => Funcao::DESENVOLVEDOR,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    $response = $this->actingAs($colaborador)->get('/dashboard');

    $response->assertStatus(200);
    $response->assertInertia(
        fn ($page) => $page->component('Dashboard')
            ->has('meusVinculos', 1)
            ->where('meusVinculos.0.projeto.id', $projeto->id)
            ->where('meusVinculos.0.funcao', 'DESENVOLVEDOR')
    );
});

test('coordenador acessa dashboard específico com estatísticas', function () {
    $coordenador = User::factory()->coordenador()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    // Criar dados para estatísticas
    Projeto::factory()->count(5)->create();
    User::factory()->count(10)->create(['status_cadastro' => StatusCadastro::ACEITO]);
    User::factory()->count(3)->create(['status_cadastro' => StatusCadastro::PENDENTE]);

    $response = $this->actingAs($coordenador)->get('/dashboard');

    $response->assertStatus(200);
    $response->assertInertia(
        fn ($page) => $page->component('DashboardCoordenador')
            ->where('projetosCount', 5)
            ->where('usuariosCount', 14) // 10 + 3 + 1 (coordenador)
            ->where('solicitacoesPendentes', 3)
            ->has('ultimosProjetos')
    );
});

test('dashboard coordenador mostra últimos 5 projetos ordenados por data', function () {
    $coordenador = User::factory()->coordenador()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    // Criar 7 projetos em datas diferentes
    $projetos = collect();
    for ($i = 0; $i < 7; $i++) {
        $projetos->push(
            Projeto::factory()->create([
                'nome' => 'Projeto '.($i + 1),
                'created_at' => now()->subDays($i),
            ])
        );
    }

    $response = $this->actingAs($coordenador)->get('/dashboard');

    $response->assertInertia(
        fn ($page) => $page->has('ultimosProjetos', 5)
            ->has(
                'ultimosProjetos.0',
                fn ($projeto) => $projeto->where('nome', 'Projeto 1')
                    ->has('id')
                    ->has('cliente')
                    ->etc()
            )
    );
});

test('dashboard colaborador mostra vínculos com informações completas', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projeto1 = Projeto::factory()->create(['nome' => 'Sistema Web']);
    $projeto2 = Projeto::factory()->create(['nome' => 'App Mobile']);

    // Vínculo aprovado
    UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto1->id,
        'status' => StatusVinculoProjeto::APROVADO,
        'tipo_vinculo' => TipoVinculo::COLABORADOR,
        'funcao' => Funcao::DESENVOLVEDOR,
        'carga_horaria' => 20,
    ]);

    // Vínculo pendente
    UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto2->id,
        'status' => StatusVinculoProjeto::PENDENTE,
        'tipo_vinculo' => TipoVinculo::COLABORADOR,
        'funcao' => Funcao::TECNICO,
        'carga_horaria' => 10,
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertInertia(
        fn ($page) => $page->has('projetos', 2)
            ->where('projetosCount', 2)
            ->has(
                'projetos.0',
                fn ($projeto) => $projeto->where('projeto_nome', 'Sistema Web')
                    ->where('status', StatusVinculoProjeto::APROVADO->value)
                    ->etc()
            )
    );
});

test('colaborador vê apenas seus próprios vínculos', function () {
    $user1 = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $user2 = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projeto = Projeto::factory()->create();

    // Vínculos para usuários diferentes
    UsuarioProjeto::factory()->create([
        'usuario_id' => $user1->id,
        'projeto_id' => $projeto->id,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    UsuarioProjeto::factory()->create([
        'usuario_id' => $user2->id,
        'projeto_id' => $projeto->id,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    $response = $this->actingAs($user1)->get('/dashboard');

    $response->assertInertia(
        fn ($page) => $page->has('projetos', 1)
            ->where('projetosCount', 1)
            ->has(
                'projetos.0',
                fn ($projeto) => $projeto->where('usuario_id', $user1->id)->etc()
            )
    );
});

test('dashboard não mostra vínculos cancelados ou rejeitados', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projeto1 = Projeto::factory()->create();
    $projeto2 = Projeto::factory()->create();
    $projeto3 = Projeto::factory()->create();

    // Vínculo aprovado
    UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto1->id,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    // Vínculo cancelado
    UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto2->id,
        'status' => StatusVinculoProjeto::ENCERRADO,
    ]);

    // Vínculo rejeitado
    UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto3->id,
        'status' => StatusVinculoProjeto::RECUSADO,
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertInertia(
        fn ($page) => $page->has('projetos', 1)
            ->where('projetosCount', 1)
    );
});

test('dashboard coordenador não conta usuários excluídos nas estatísticas', function () {
    $coordenador = User::factory()->coordenador()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    // Usuários APROVADOs
    User::factory()->count(5)->create(['status_cadastro' => StatusCadastro::ACEITO]);

    // Usuários excluídos (soft delete)
    $usuariosExcluidos = User::factory()->count(3)->create(['status_cadastro' => StatusCadastro::ACEITO]);
    foreach ($usuariosExcluidos as $usuario) {
        $usuario->delete();
    }

    $response = $this->actingAs($coordenador)->get('/dashboard');

    $response->assertInertia(
        fn ($page) => $page->where('usuariosCount', 6) // 5 + 1 (coordenador)
    );
});

test('dashboard com projetos sem dados opcionais', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projeto = Projeto::factory()->create([
        'nome' => 'Projeto Mínimo',
        'cliente' => 'Cliente',
        'descricao' => null,
        'data_termino' => null,
    ]);

    UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'status' => StatusVinculoProjeto::APROVADO,
        'data_fim' => null,
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);
    $response->assertInertia(
        fn ($page) => $page->has('projetos', 1)
            ->has(
                'projetos.0',
                fn ($projeto) => $projeto->where('projeto_nome', 'Projeto Mínimo')
                    ->etc()
            )
    );
});

test('dashboard mantém performance com muitos vínculos', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    // Criar 50 projetos e vínculos
    $projetos = Projeto::factory()->count(50)->create();

    foreach ($projetos as $projeto) {
        UsuarioProjeto::factory()->create([
            'usuario_id' => $user->id,
            'projeto_id' => $projeto->id,
            'status' => StatusVinculoProjeto::APROVADO,
        ]);
    }

    $start = microtime(true);
    $response = $this->actingAs($user)->get('/dashboard');
    $executionTime = microtime(true) - $start;

    $response->assertStatus(200);
    $response->assertInertia(
        fn ($page) => $page->has('projetos', 50)
            ->where('projetosCount', 50)
    );

    // Verificar que não demora mais que 2 segundos
    expect($executionTime)->toBeLessThan(2.0);
});

test('dashboard coordenador com zero estatísticas', function () {
    $coordenador = User::factory()->coordenador()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($coordenador)->get('/dashboard');

    $response->assertInertia(
        fn ($page) => $page->where('projetosCount', 0)
            ->where('usuariosCount', 1) // apenas o coordenador
            ->where('solicitacoesPendentes', 0)
            ->has('ultimosProjetos', 0)
    );
});

test('dashboard colaborador sem vínculos', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertInertia(
        fn ($page) => $page->has('projetos', 0)
            ->where('projetosCount', 0)
    );
});
