<?php

namespace App\Models\Concerns;

use App\Enums\Role;
use App\Enums\Funcao;
use App\Enums\StatusVinculoProjeto;

trait HasRole
{
    /**
     * Get the user's role.
     *
     * @return \App\Enums\Role
     */
    public function getRoleAttribute(): Role
    {
        if ($this->is_coordenador_master) {
            return Role::COORDENADOR_MASTER;
        }

        if ($this->isCoordenadorDeProjeto()) {
            return Role::COORDENADOR;
        }

        return Role::COLABORADOR;
    }

    /**
     * Check if the user is a master coordinator.
     *
     * @return bool
     */
    public function isCoordenadorMaster(): bool
    {
        return $this->role === Role::COORDENADOR_MASTER;
    }

    /**
     * Check if the user is a project coordinator.
     *
     * @return bool
     */
    public function isCoordenador(): bool
    {
        return $this->role === Role::COORDENADOR || $this->role === Role::COORDENADOR_MASTER;
    }

    /**
     * Check if the user is a collaborator.
     *
     * @return bool
     */
    public function isColaborador(): bool
    {
        return $this->role === Role::COLABORADOR;
    }

    /**
     * Helper function to check if a user is a coordinator of any active project.
     *
     * @return bool
     */
    protected function isCoordenadorDeProjeto(): bool
    {
        return $this->projetos()
            ->where('funcao', Funcao::COORDENADOR)
            ->where('status', StatusVinculoProjeto::APROVADO)
            ->exists();
    }
}
