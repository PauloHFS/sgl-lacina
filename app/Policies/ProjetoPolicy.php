<?php

namespace App\Policies;

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
     * Determine whether the user can view the absences of a project.
     */
    public function viewAusencias(User $user, Projeto $projeto): bool
    {
        return $user->isCoordenador($projeto);
    }
}