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
        $original = $usuarioProjeto->getOriginal();

        if ($usuarioProjeto->skipHistoryLog) {
            $originalDataInicio = isset($original['data_inicio']) ? $original['data_inicio'] : null;
            $currentDataInicio = $usuarioProjeto->data_inicio;

            // Check if data_inicio actually changed its value
            if ($currentDataInicio && $originalDataInicio && $currentDataInicio->format('Y-m-d') !== $originalDataInicio->format('Y-m-d')) {
                // Find the second to last history entry
                $secondToLastHistory = HistoricoUsuarioProjeto::where('usuario_id', $usuarioProjeto->usuario_id)
                    ->where('projeto_id', $usuarioProjeto->projeto_id)
                    ->orderByDesc('created_at')
                    ->offset(1) // Get the second one
                    ->first();

                if ($secondToLastHistory) {
                    // Calculate the new data_fim for the second to last entry
                    $newEndDateForSecondToLast = $currentDataInicio->copy()->subDay();

                    // Only update if the new end date is valid and after the second to last history's start date
                    if ($newEndDateForSecondToLast && $secondToLastHistory->data_inicio->lte($newEndDateForSecondToLast)) {
                        $secondToLastHistory->update(['data_fim' => $newEndDateForSecondToLast]);
                    } else if ($newEndDateForSecondToLast && $secondToLastHistory->data_inicio->gt($newEndDateForSecondToLast)) {
                        // If the calculated end date is before the start date, end on its start date.
                        $secondToLastHistory->update(['data_fim' => $secondToLastHistory->data_inicio]);
                    }
                }
            }
            $this->updateLastHistory($usuarioProjeto);
            return;
        }

        $original = $usuarioProjeto->getOriginal();

        // Check if only 'data_fim' has changed
        $onlyDataFimChanged = $usuarioProjeto->isDirty('data_fim') &&
                              !$usuarioProjeto->isDirty('data_inicio') &&
                              !$usuarioProjeto->isDirty('status') &&
                              !$usuarioProjeto->isDirty('funcao') &&
                              !$usuarioProjeto->isDirty('carga_horaria') &&
                              !$usuarioProjeto->isDirty('tipo_vinculo') &&
                              !$usuarioProjeto->isDirty('valor_bolsa') &&
                              !$usuarioProjeto->isDirty('trocar');

        if ($onlyDataFimChanged) {
            // If only data_fim changed, update the last history entry's data_fim
            $lastHistory = HistoricoUsuarioProjeto::where('usuario_id', $usuarioProjeto->usuario_id)
                ->where('projeto_id', $usuarioProjeto->projeto_id)
                ->orderByDesc('created_at')
                ->first(); // Get the very last history entry, active or not

            if ($lastHistory) {
                $lastHistory->update(['data_fim' => $usuarioProjeto->data_fim]);
            }
            return; // Exit, as no new history entry is needed
        }

        // If data_inicio changed, or any other relevant field changed (excluding data_fim alone)
        $relevantFieldsChanged = false;
        $camposVinculo = [
            'status',
            'funcao',
            'carga_horaria',
            'data_inicio',
            'tipo_vinculo',
            'valor_bolsa',
            'trocar',
        ];

        foreach ($camposVinculo as $field) {
            if ($usuarioProjeto->isDirty($field)) {
                // Special handling for dates: only consider dirty if the date value actually changed
                if (in_array($field, ['data_inicio'])) {
                    $currentDate = $usuarioProjeto->$field ? $usuarioProjeto->$field->format('Y-m-d') : null;
                    $originalDate = isset($original[$field]) ? $original[$field]->format('Y-m-d') : null;
                    if ($currentDate !== $originalDate) {
                        $relevantFieldsChanged = true;
                        break;
                    }
                } else {
                    $relevantFieldsChanged = true;
                    break;
                }
            }
        }

        if ($relevantFieldsChanged) {
            // Find the last active history entry for this user and project
            $lastActiveHistory = HistoricoUsuarioProjeto::where('usuario_id', $usuarioProjeto->usuario_id)
                ->where('projeto_id', $usuarioProjeto->projeto_id)
                ->whereNull('data_fim') // Only active history
                ->orderByDesc('created_at')
                ->first();

            // Determine the end date for the previous active history entry
            $previousHistoryEndDate = null;

            if ($usuarioProjeto->isDirty('data_inicio')) {
                // If data_inicio changed, the previous history should end the day before the new data_inicio
                if ($usuarioProjeto->data_inicio) {
                    $previousHistoryEndDate = $usuarioProjeto->data_inicio->copy()->subDay();
                } else {
                    // If data_inicio is being set to null (unlikely for a new period), end today.
                    $previousHistoryEndDate = now();
                }
            } else {
                // If other fields changed, but not data_inicio, end the previous history today.
                $previousHistoryEndDate = now();
            }

            // Update the end date of the last active history entry
            if ($lastActiveHistory) {
                // Only update if the previousHistoryEndDate is valid and after the history's start date
                if ($previousHistoryEndDate && $lastActiveHistory->data_inicio->lte($previousHistoryEndDate)) {
                    $lastActiveHistory->update(['data_fim' => $previousHistoryEndDate]);
                } else if ($previousHistoryEndDate && $lastActiveHistory->data_inicio->gt($previousHistoryEndDate)) {
                    // If the calculated end date is before the start date, end on its start date.
                    $lastActiveHistory->update(['data_fim' => $lastActiveHistory->data_inicio]);
                }
            }

            // Create a new history entry with the current state of the UsuarioProjeto
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
            'valor_bolsa' => $usuarioProjeto->valor_bolsa ?? 0,
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
                'valor_bolsa' => $usuarioProjeto->valor_bolsa ?? 0,
            ]);
        }
    }

    /**
     * Finaliza o histórico anterior e cria um novo ao alterar qualquer característica do vínculo.
     */
    protected function updateLastHistoryEndDate(UsuarioProjeto $usuarioProjeto, $endDate): void
    {
        $lastHistory = HistoricoUsuarioProjeto::where('usuario_id', $usuarioProjeto->usuario_id)
            ->where('projeto_id', $usuarioProjeto->projeto_id)
            ->whereNull('data_fim') // Only update active history
            ->orderByDesc('created_at')
            ->first();

        if ($lastHistory) {
            $lastHistory->update([
                'data_fim' => $endDate,
            ]);
        }
    }
}