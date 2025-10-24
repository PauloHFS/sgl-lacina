<?php

namespace App\Policies;

use App\Models\Sala;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SalaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Todos os usuários autenticados podem ver as salas
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Sala $sala): bool
    {
        // Todos os usuários autenticados podem ver detalhes de uma sala
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Apenas administradores ou docentes podem criar salas
        // Por enquanto, permitindo para todos os usuários autenticados
        // Implementar lógica de roles quando necessário
        return true;
    }

    /**
     * Determine whether the user can update any model.
     */
    public function updateAny(User $user): bool
    {
        // Apenas administradores ou docentes podem atualizar salas
        // Por enquanto, permitindo para todos os usuários autenticados
        // Implementar lógica de roles quando necessário
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Sala $sala): bool
    {
        // Apenas administradores ou docentes podem atualizar salas
        // Por enquanto, permitindo para todos os usuários autenticados
        // Implementar lógica de roles quando necessário
        return true;
    }

    /**
     * Determine whether the user can delete any model.
     */
    public function deleteAny(User $user): bool
    {
        // Apenas administradores ou docentes podem deletar salas
        // Por enquanto, permitindo para todos os usuários autenticados
        // Implementar lógica de roles quando necessário
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Sala $sala): bool
    {
        // Apenas administradores ou docentes podem deletar salas
        // Por enquanto, permitindo para todos os usuários autenticados
        // Implementar lógica de roles quando necessário
        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Sala $sala): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Sala $sala): bool
    {
        return false;
    }
}
