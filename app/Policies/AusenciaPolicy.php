<?php

namespace App\Policies;

use App\Enums\Funcao;
use App\Enums\StatusVinculoProjeto;
use App\Models\Ausencia;
use App\Models\User;
use App\Models\UsuarioProjeto;

class AusenciaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Ausencia $ausencia): bool
    {
        // O usuário pode ver a ausência se for o dono ou se for coordenador do projeto.
        if ($ausencia->usuario_id === $user->id) {
            return true;
        }

        return $this->isCoordenadorDoProjeto($user, $ausencia->projeto_id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Ausencia $ausencia): bool
    {
        // Apenas o dono pode editar uma ausência RECUSADA e PENDENTE.
        return $ausencia->usuario_id === $user->id && $ausencia->status !== \App\Enums\StatusAusencia::APROVADO->value;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Ausencia $ausencia): bool
    {
        // Apenas o dono pode deletar uma ausência RECUSADA e PENDENTE.
        return $ausencia->usuario_id === $user->id && $ausencia->status !== \App\Enums\StatusAusencia::APROVADO->value;
    }

    /**
     * Determine whether the user can update the model's status.
     */
    public function updateStatus(User $user, Ausencia $ausencia): bool
    {
        // Apenas um coordenador do projeto específico pode aprovar ou recusar.
        return $this->isCoordenadorDoProjeto($user, $ausencia->projeto_id);
    }

    /**
     * Helper function to check if a user is a coordinator of a specific project.
     */
    protected function isCoordenadorDoProjeto(User $user, string $projeto_id): bool
    {
        return UsuarioProjeto::where('usuario_id', $user->id)
            ->where('projeto_id', $projeto_id)
            ->where('funcao', Funcao::COORDENADOR)
            ->where('status', StatusVinculoProjeto::APROVADO)
            ->exists();
    }
}
