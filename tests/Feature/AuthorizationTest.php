<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Projeto;
use App\Enums\Funcao;
use App\Enums\StatusVinculoProjeto;
use App\Enums\Role;
use Illuminate\Support\Facades\Route;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Define test routes with the 'role' middleware
        Route::middleware(['web', 'auth', 'role:colaborador'])->get('/test/colaborador', function () {
            return response('colaborador content');
        });
        Route::middleware(['web', 'auth', 'role:coordenador'])->get('/test/coordenador', function () {
            return response('coordenador content');
        });
        Route::middleware(['web', 'auth', 'role:coordenador_master'])->get('/test/master', function () {
            return response('master content');
        });
    }

    /** @test */
    public function unauthenticated_user_cannot_access_any_protected_route()
    {
        $this->get('/test/colaborador')->assertRedirect('/login');
        $this->get('/test/coordenador')->assertRedirect('/login');
        $this->get('/test/master')->assertRedirect('/login');
    }

    /** @test */
    public function colaborador_can_only_access_colaborador_routes()
    {
        $user = User::factory()->create(); // Defaults to colaborador
        $this->actingAs($user);

        $this->get('/test/colaborador')->assertOk();
        $this->get('/test/coordenador')->assertForbidden();
        $this->get('/test/master')->assertForbidden();
    }

    /** @test */
    public function coordenador_can_access_coordenador_and_colaborador_routes()
    {
        $user = User::factory()->create();
        $project = Projeto::factory()->create();
        $user->projetos()->attach($project->id, [
            'funcao' => Funcao::COORDENADOR,
            'status' => StatusVinculoProjeto::APROVADO,
            'tipo_vinculo' => \App\Enums\TipoVinculo::COORDENADOR,
            'data_inicio' => now(),
        ]);
        $user->refresh(); // Role is now COORDENADOR

        $this->actingAs($user);

        $this->get('/test/colaborador')->assertOk();
        $this->get('/test/coordenador')->assertOk();
        $this->get('/test/master')->assertForbidden();
    }

    /** @test */
    public function coordenador_master_can_access_all_routes()
    {
        $user = User::factory()->create(['is_coordenador_master' => true]);

        $this->actingAs($user);

        $this->get('/test/colaborador')->assertOk();
        $this->get('/test/coordenador')->assertOk();
        $this->get('/test/master')->assertOk();
    }
}