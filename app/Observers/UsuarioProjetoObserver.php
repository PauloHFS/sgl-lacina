<?php

namespace App\Observers;

use App\Models\UsuarioProjeto;
use App\Models\HistoricoUsuarioProjeto;

class UsuarioProjetoObserver
{
    /**
     * Handle the UsuarioProjeto "created" event.
     */
    public function created(UsuarioProjeto $usuarioProjeto): void
    {
        // $this->logHistory($usuarioProjeto);
    }

    /**
     * Handle the UsuarioProjeto "updated" event.
     */
    public function updated(UsuarioProjeto $usuarioProjeto): void
    {
        if ($usuarioProjeto->isDirty(['status', 'funcao', 'carga_horaria_semanal', 'data_fim', 'tipo_vinculo'])) {
            $this->logHistory($usuarioProjeto);
        }
    }

    /**
     * Handle the UsuarioProjeto "deleted" event.
     */
    public function deleted(UsuarioProjeto $usuarioProjeto): void
    {
        // $this->logHistory($usuarioProjeto, 'REMOVIDO_LOGICAMENTE');
    }

    /**
     * Handle the UsuarioProjeto "restored" event.
     */
    public function restored(UsuarioProjeto $usuarioProjeto): void
    {
        //
    }

    /**
     * Handle the UsuarioProjeto "force deleted" event.
     */
    public function forceDeleted(UsuarioProjeto $usuarioProjeto): void
    {
        //
    }

    /**
     * Log the current state of the UsuarioProjeto to the history table.
     */
    protected function logHistory(UsuarioProjeto $usuarioProjeto, ?string $customStatus = null): void
    {
        HistoricoUsuarioProjeto::create([
            'usuario_id' => $usuarioProjeto->usuario_id,
            'projeto_id' => $usuarioProjeto->projeto_id,
            'tipo_vinculo' => $usuarioProjeto->tipo_vinculo,
            'funcao' => $usuarioProjeto->funcao,
            'status' => $customStatus ?? $usuarioProjeto->status,
            'carga_horaria_semanal' => $usuarioProjeto->carga_horaria_semanal,
            'data_inicio' => $usuarioProjeto->data_inicio,
            'data_fim' => $usuarioProjeto->data_fim,
        ]);
    }
}
