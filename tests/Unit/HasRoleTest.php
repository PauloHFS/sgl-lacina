<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Projeto;
use App\Enums\Role;
use App\Enums\Funcao;
use App\Enums\StatusVinculoProjeto;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HasRoleTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_colaborador_role_by_default()
    {
        $user = User::factory()->create();

        $this->assertEquals(Role::COLABORADOR, $user->role);
    }

    /** @test */
    public function it_returns_coordenador_master_role_when_flag_is_true()
    {
        $user = User::factory()->create(['is_coordenador_master' => true]);

        $this->assertEquals(Role::COORDENADOR_MASTER, $user->role);
    }

    /** @test */
    public function it_returns_coordenador_role_when_user_is_a_project_coordinator()
    {
        $user = User::factory()->create();
        $project = Projeto::factory()->create();

        $user->projetos()->attach($project->id, [
            'funcao' => Funcao::COORDENADOR,
            'status' => StatusVinculoProjeto::APROVADO,
            'tipo_vinculo' => \App\Enums\TipoVinculo::COORDENADOR,
            'data_inicio' => now(),
        ]);

        // We need to refresh the model to get the relationship loaded for the accessor
        $user->refresh();

        $this->assertEquals(Role::COORDENADOR, $user->role);
    }

    /** @test */
    public function it_returns_colaborador_role_when_user_is_just_a_collaborator()
    {
        $user = User::factory()->create();
        $project = Projeto::factory()->create();

        $user->projetos()->attach($project->id, [
            'funcao' => Funcao::DESENVOLVEDOR,
            'status' => StatusVinculoProjeto::APROVADO,
            'tipo_vinculo' => \App\Enums\TipoVinculo::COLABORADOR,
            'data_inicio' => now(),
        ]);

        $user->refresh();

        $this->assertEquals(Role::COLABORADOR, $user->role);
    }

    /** @test */
    public function coordenador_master_role_overrides_coordenador_role()
    {
        $user = User::factory()->create(['is_coordenador_master' => true]);
        $project = Projeto::factory()->create();

        $user->projetos()->attach($project->id, [
            'funcao' => Funcao::COORDENADOR,
            'status' => StatusVinculoProjeto::APROVADO,
            'tipo_vinculo' => \App\Enums\TipoVinculo::COORDENADOR,
            'data_inicio' => now(),
        ]);

        $user->refresh();

        $this->assertEquals(Role::COORDENADOR_MASTER, $user->role);
    }

    /** @test */
    public function is_coordenador_helper_returns_true_for_both_coordenador_roles()
    {
        $master = User::factory()->create(['is_coordenador_master' => true]);

        $coordinator = User::factory()->create();
        $project = Projeto::factory()->create();
        $coordinator->projetos()->attach($project->id, [
            'funcao' => Funcao::COORDENADOR,
            'status' => StatusVinculoProjeto::APROVADO,
            'tipo_vinculo' => \App\Enums\TipoVinculo::COORDENADOR,
            'data_inicio' => now(),
        ]);
        $coordinator->refresh();

        $colaborador = User::factory()->create();

        $this->assertTrue($master->isCoordenador());
        $this->assertTrue($coordinator->isCoordenador());
        $this->assertFalse($colaborador->isCoordenador());
    }
}
