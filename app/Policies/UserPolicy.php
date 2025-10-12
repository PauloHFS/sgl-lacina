<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \\App\\Models\\User  $user
     * @param  \\App\\Models\\User  $model
     * @return \\Illuminate\\Auth\\Access\\Response|bool
     */
    public function view(User $user, User $model): bool
    {
        // Permite que o próprio usuário visualize seu perfil.
        // O COORDENADOR_MASTER pode visualizar qualquer perfil através do Gate::before.
        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \\App\\Models\\User  $user
     * @param  \\App\\Models\\User  $model
     * @return \\Illuminate\\Auth\\Access\\Response|bool
     */
    public function update(User $user, User $model): bool
    {
        // Permite que o próprio usuário atualize seu perfil.
        // O COORDENADOR_MASTER pode atualizar qualquer perfil através do Gate::before.
        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }
}
