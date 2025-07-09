<?php

namespace App\Observers;

use App\Enums\StatusVinculoProjeto;
use App\Models\UsuarioProjeto;
use App\Models\HistoricoUsuarioProjeto;

class UsuarioProjetoObserver
{
    /**
     * Handle the UsuarioProjeto "created" event.
     */
    public function created(UsuarioProjeto $usuarioProjeto): void
    {
        $this->logHistory($usuarioProjeto);
    }

    /**
     * Handle the UsuarioProjeto "updated" event.
     */
    public function updated(UsuarioProjeto $usuarioProjeto): void
    {
        if ($usuarioProjeto->skipHistoryLog) {
            $this->updateLastHistory($usuarioProjeto);
            return;
        }

        $camposVinculo = [
            'status',
            'funcao',
            'carga_horaria',
            'data_inicio',
            'data_fim',
            'tipo_vinculo',
            'valor_bolsa',
        ];

        if ($usuarioProjeto->isDirty($camposVinculo)) {
            $this->finalizarERegistrarNovoHistorico($usuarioProjeto, $camposVinculo);
        }

        if ($usuarioProjeto->isDirty(['status', 'funcao', 'carga_horaria', 'data_inicio', 'data_fim', 'tipo_vinculo', 'valor_bolsa'])) {
            $this->logHistory($usuarioProjeto);
        }
    }

    /**
     * Handle the UsuarioProjeto "deleted" event.
     */
    public function deleted(UsuarioProjeto $usuarioProjeto): void
    {
        $this->logHistory($usuarioProjeto, StatusVinculoProjeto::ENCERRADO->value);
    }

    /**
     * Handle the UsuarioProjeto "restored" event.
     */
    public function restored(UsuarioProjeto $usuarioProjeto): void
    {
        $this->logHistory($usuarioProjeto);
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
            'trocar' => $usuarioProjeto->trocar ?? false,
            'tipo_vinculo' => $usuarioProjeto->tipo_vinculo,
            'funcao' => $usuarioProjeto->funcao,
            'status' => $customStatus ?? $usuarioProjeto->status,
            'carga_horaria' => $usuarioProjeto->carga_horaria,
            'data_inicio' => $usuarioProjeto->data_inicio,
            'data_fim' => $usuarioProjeto->data_fim,
            'valor_bolsa' => $usuarioProjeto->valor_bolsa,
        ]);
    }

    /**
     * Atualiza a última entrada do histórico com os dados atuais do vínculo.
     */
    protected function updateLastHistory(UsuarioProjeto $usuarioProjeto): void
    {
        $lastHistory = HistoricoUsuarioProjeto::where('usuario_id', $usuarioProjeto->usuario_id)
            ->where('projeto_id', $usuarioProjeto->projeto_id)
            ->orderByDesc('created_at')
            ->first();

        if ($lastHistory) {
            $lastHistory->update([
                'trocar' => $usuarioProjeto->trocar ?? false,
                'tipo_vinculo' => $usuarioProjeto->tipo_vinculo,
                'funcao' => $usuarioProjeto->funcao,
                'status' => $usuarioProjeto->status,
                'carga_horaria' => $usuarioProjeto->carga_horaria,
                'data_inicio' => $usuarioProjeto->data_inicio,
                'data_fim' => $usuarioProjeto->data_fim,
                'valor_bolsa' => $usuarioProjeto->valor_bolsa,
            ]);
        }
    }

    /**
     * Finaliza o histórico anterior e cria um novo ao alterar qualquer característica do vínculo.
     */
    protected function finalizarERegistrarNovoHistorico(UsuarioProjeto $usuarioProjeto, array $camposVinculo): void
    {
        $hoje = now();

        $lastHistory = HistoricoUsuarioProjeto::where('usuario_id', $usuarioProjeto->usuario_id)
            ->where('projeto_id', $usuarioProjeto->projeto_id)
            ->orderByDesc('created_at')
            ->first();

        if ($lastHistory && is_null($lastHistory->data_fim)) {
            $lastHistory->update([
                'data_fim' => $hoje,
            ]);
        }

        // Cria novo histórico com data_inicio = agora
        HistoricoUsuarioProjeto::create([
            'usuario_id' => $usuarioProjeto->usuario_id,
            'projeto_id' => $usuarioProjeto->projeto_id,
            'trocar' => $usuarioProjeto->trocar ?? false,
            'tipo_vinculo' => $usuarioProjeto->tipo_vinculo,
            'funcao' => $usuarioProjeto->funcao,
            'status' => $usuarioProjeto->status,
            'carga_horaria' => $usuarioProjeto->carga_horaria,
            'data_inicio' => $hoje,
            'data_fim' => null,
            'valor_bolsa' => $usuarioProjeto->valor_bolsa,
        ]);
    }
}
