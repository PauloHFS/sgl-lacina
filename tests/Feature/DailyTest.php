<?php

use App\Models\Daily;
use App\Models\Projeto;
use App\Models\User;
use App\Models\UsuarioProjeto;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'status_cadastro' => 'APROVADO',
    ]);

    $this->projeto = Projeto::factory()->create();

    $this->usuarioProjeto = UsuarioProjeto::factory()->create([
        'usuario_id' => $this->user->id,
        'projeto_id' => $this->projeto->id,
        'status' => 'ATIVO',
        'funcao' => 'Desenvolvedor',
        'tipo_vinculo' => 'bolsista',
        'carga_horaria' => 20,
        'data_inicio' => now(),
    ]);

    $this->actingAs($this->user);
});

test('pode acessar a página de listagem de dailies', function () {
    $response = $this->get(route('daily.index'));

    $response->assertStatus(200);
    $response->assertInertia(
        fn($page) => $page
            ->component('Daily/Index')
            ->has('dailies')
    );
});

test('pode acessar a página de criação de daily', function () {
    $response = $this->get(route('daily.create'));

    $response->assertStatus(200);
    $response->assertInertia(
        fn($page) => $page
            ->component('Daily/Create')
            ->has('usuarioProjetos')
    );
});

test('pode criar um daily com dados válidos', function () {
    $dailyData = [
        'usuario_projeto_id' => $this->usuarioProjeto->id,
        'data' => now()->format('Y-m-d'),
        'ontem' => 'Trabalhei nas funcionalidades do backend',
        'observacoes' => 'Tudo funcionou bem',
        'hoje' => 'Vou trabalhar no frontend',
        'carga_horaria' => 8,
    ];

    $response = $this->post(route('daily.store'), $dailyData);

    $response->assertRedirect(route('daily.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('dailies', [
        'usuario_id' => $this->user->id,
        'usuario_projeto_id' => $this->usuarioProjeto->id,
        'data' => $dailyData['data'],
        'ontem' => $dailyData['ontem'],
        'observacoes' => $dailyData['observacoes'],
        'hoje' => $dailyData['hoje'],
        'carga_horaria' => $dailyData['carga_horaria'],
    ]);
});

test('não pode criar daily com dados inválidos', function () {
    $response = $this->post(route('daily.store'), []);

    $response->assertSessionHasErrors([
        'usuario_projeto_id',
        'data',
        'ontem',
        'hoje',
        'carga_horaria',
    ]);
});

test('não pode criar daily para projeto que não tem acesso', function () {
    $outroUsuario = User::factory()->create();
    $outroProjeto = Projeto::factory()->create();
    $outroUsuarioProjeto = UsuarioProjeto::factory()->create([
        'usuario_id' => $outroUsuario->id,
        'projeto_id' => $outroProjeto->id,
        'status' => 'ATIVO',
    ]);

    $dailyData = [
        'usuario_projeto_id' => $outroUsuarioProjeto->id,
        'data' => now()->format('Y-m-d'),
        'ontem' => 'Trabalhei nas funcionalidades',
        'hoje' => 'Vou trabalhar mais',
        'carga_horaria' => 8,
    ];

    $response = $this->post(route('daily.store'), $dailyData);

    $response->assertSessionHasErrors(['usuario_projeto_id']);
});

test('não pode criar daily duplicado para mesmo projeto e data', function () {
    // Criar primeiro daily
    Daily::factory()->create([
        'usuario_id' => $this->user->id,
        'usuario_projeto_id' => $this->usuarioProjeto->id,
        'data' => now()->format('Y-m-d'),
    ]);

    // Tentar criar segundo daily para mesma data/projeto
    $dailyData = [
        'usuario_projeto_id' => $this->usuarioProjeto->id,
        'data' => now()->format('Y-m-d'),
        'ontem' => 'Trabalhei nas funcionalidades',
        'hoje' => 'Vou trabalhar mais',
        'carga_horaria' => 8,
    ];

    $response = $this->post(route('daily.store'), $dailyData);

    $response->assertSessionHasErrors(['data']);
});

test('pode visualizar um daily próprio', function () {
    $daily = Daily::factory()->create([
        'usuario_id' => $this->user->id,
        'usuario_projeto_id' => $this->usuarioProjeto->id,
    ]);

    $response = $this->get(route('daily.show', $daily));

    $response->assertStatus(200);
    $response->assertInertia(
        fn($page) => $page
            ->component('Daily/Show')
            ->has('daily')
            ->where('daily.id', $daily->id)
    );
});

test('não pode visualizar daily de outro usuário', function () {
    $outroUsuario = User::factory()->create();
    $outroProjeto = Projeto::factory()->create();
    $outroUsuarioProjeto = UsuarioProjeto::factory()->create([
        'usuario_id' => $outroUsuario->id,
        'projeto_id' => $outroProjeto->id,
    ]);

    $daily = Daily::factory()->create([
        'usuario_id' => $outroUsuario->id,
        'usuario_projeto_id' => $outroUsuarioProjeto->id,
    ]);

    $response = $this->get(route('daily.show', $daily));

    $response->assertStatus(403);
});

test('pode editar daily do dia atual', function () {
    $daily = Daily::factory()->create([
        'usuario_id' => $this->user->id,
        'usuario_projeto_id' => $this->usuarioProjeto->id,
        'data' => now()->format('Y-m-d'),
    ]);

    $response = $this->get(route('daily.edit', $daily));

    $response->assertStatus(200);
    $response->assertInertia(
        fn($page) => $page
            ->component('Daily/Edit')
            ->has('daily')
            ->has('usuarioProjetos')
    );
});

test('não pode editar daily de dia anterior', function () {
    $daily = Daily::factory()->create([
        'usuario_id' => $this->user->id,
        'usuario_projeto_id' => $this->usuarioProjeto->id,
        'data' => now()->subDay()->format('Y-m-d'),
    ]);

    $response = $this->get(route('daily.edit', $daily));

    $response->assertRedirect(route('daily.index'));
    $response->assertSessionHasErrors(['error']);
});

test('pode atualizar daily com dados válidos', function () {
    $daily = Daily::factory()->create([
        'usuario_id' => $this->user->id,
        'usuario_projeto_id' => $this->usuarioProjeto->id,
        'data' => now()->format('Y-m-d'),
    ]);

    $updateData = [
        'usuario_projeto_id' => $this->usuarioProjeto->id,
        'data' => now()->format('Y-m-d'),
        'ontem' => 'Trabalhei nas funcionalidades atualizadas',
        'observacoes' => 'Atualizando observações',
        'hoje' => 'Vou trabalhar em novas features',
        'carga_horaria' => 6,
    ];

    $response = $this->put(route('daily.update', $daily), $updateData);

    $response->assertRedirect(route('daily.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('dailies', [
        'id' => $daily->id,
        'ontem' => $updateData['ontem'],
        'observacoes' => $updateData['observacoes'],
        'hoje' => $updateData['hoje'],
        'carga_horaria' => $updateData['carga_horaria'],
    ]);
});

test('pode excluir daily do dia atual', function () {
    $daily = Daily::factory()->create([
        'usuario_id' => $this->user->id,
        'usuario_projeto_id' => $this->usuarioProjeto->id,
        'data' => now()->format('Y-m-d'),
    ]);

    $response = $this->delete(route('daily.destroy', $daily));

    $response->assertRedirect(route('daily.index'));
    $response->assertSessionHas('success');

    $this->assertSoftDeleted('dailies', ['id' => $daily->id]);
});

test('não pode excluir daily de dia anterior', function () {
    $daily = Daily::factory()->create([
        'usuario_id' => $this->user->id,
        'usuario_projeto_id' => $this->usuarioProjeto->id,
        'data' => now()->subDay()->format('Y-m-d'),
    ]);

    $response = $this->delete(route('daily.destroy', $daily));

    $response->assertRedirect(route('daily.index'));
    $response->assertSessionHasErrors(['error']);

    $this->assertDatabaseHas('dailies', ['id' => $daily->id]);
});
