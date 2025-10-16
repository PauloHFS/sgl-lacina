<?php

namespace App\Policies;

use App\Enums\StatusVinculoProjeto;
use App\Enums\TipoVinculo;
use App\Models\Projeto;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProjetoPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Projeto $projeto): bool
    {
        // Allow user to view project if they are part of it.
        return $user->projetos()->where('projeto_id', $projeto->id)->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only COORDENADOR_MASTER can create, handled by Gate::before.
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Projeto $projeto): bool
    {
        // Handled by Gate::before for COORDENADOR_MASTER.
        // For COORDENADOR, check if they are an approved coordinator of this specific project.
        return $user->projetos()
            ->where('projeto_id', $projeto->id)
            ->where('tipo_vinculo', TipoVinculo::COORDENADOR)
            ->where('status', StatusVinculoProjeto::APROVADO)
            ->exists();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Projeto $projeto): bool
    {
        // Only COORDENADOR_MASTER can delete, handled by Gate::before.
        return false;
    }

    /**
     * Determine whether the user can view the absences of a project.
     */
    public function viewAusencias(User $user, Projeto $projeto): bool
    {
        return $this->update($user, $projeto);
    }
}
