<?php

namespace App\Http\Controllers;

use App\Enums\TipoHorario;
use App\Models\Baia;
use App\Models\Sala;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class HorarioController extends Controller
{

    public function index(Request $request)
    {
        $horarios = $request->user()->horarios()
            ->with([
                'usuarioProjeto.projeto:id,nome',
                'baia:id,nome,sala_id',
                'baia.sala:id,nome'
            ])
            ->orderBy('dia_da_semana')
            ->orderBy('horario', 'asc')
            ->get()
            ->groupBy('dia_da_semana');

        return Inertia::render('Horarios/Index', [
            'horarios' => $horarios,
        ]);
    }

    /**
     * Mostra o formulário para editar horários do usuário.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Inertia\Response
     */
    public function edit(Request $request)
    {
        $horarios = $request->user()->horarios()
            ->with([
                'usuarioProjeto.projeto:id,nome',
                'baia:id,nome,sala_id',
                'baia.sala:id,nome'
            ])
            ->orderBy('dia_da_semana')
            ->orderBy('horario', 'asc')
            ->get()
            ->groupBy('dia_da_semana');

        $salas = Sala::ativas()
            ->with(['baias' => function ($query) {
                $query->ativas()->orderBy('nome');
            }])
            ->orderBy('nome')
            ->get(['id', 'nome']);

        return Inertia::render('Horarios/Edit', [
            'horarios' => $horarios,
            'salas' => $salas,
        ]);
    }

    /**
     * Atualiza os horários do usuário com base nos dados validados da requisição.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'horarios' => 'required|array',
            'horarios.*.id' => 'required|exists:horarios,id',
            'horarios.*.tipo' => 'nullable|string|in:' . implode(',', array_map(fn($e) => $e->value, TipoHorario::cases())),
            'horarios.*.usuario_projeto_id' => 'nullable|exists:usuario_projeto,id',
            'horarios.*.baia_id' => 'nullable|exists:baias,id',
            'horarios.*.baia_updated_at' => 'nullable|date', // Timestamp da baia para locking otimista
        ], [
            'horarios.*.tipo.in' => 'O tipo de horário deve ser válido.',
            'horarios.*.usuario_projeto_id.exists' => 'O projeto selecionado não existe.',
            'horarios.*.baia_id.exists' => 'A baia selecionada não existe.',
        ]);

        Log::info('Horários atualizados', [
            'user_id' => $request->user()->id,
            'horarios' => $validatedData['horarios'],
        ]);

        // Validação customizada para garantir que apenas um campo seja preenchido por vez
        $customErrors = [];
        // foreach ($validatedData['horarios'] as $index => $horario) {
        //     $fieldsPresent = collect(['tipo', 'usuario_projeto_id', 'baia_id'])
        //         ->filter(fn($field) => !empty($horario[$field]))
        //         ->count();

        //     if ($fieldsPresent !== 1) {
        //         $customErrors["horarios.{$index}"] = 'Deve ser informado apenas um campo: tipo, projeto ou baia.';
        //     }
        // }

        // pega os horarios do usuário que estão sendo atualizados
        $horarios = $request->user()->horarios()
            ->whereIn('id', collect($validatedData['horarios'])->pluck('id'))
            ->get();

        foreach ($validatedData['horarios'] as $index => $horario) {
            $oldHorario = $horarios->firstWhere('id', $horario['id']);

            if (!$oldHorario) {
                $customErrors["horarios.{$index}.id"] = 'Horário não encontrado.';
                continue;
            }

            // Validação de locking otimista para baias
            if (!empty($horario['baia_id']) && !empty($horario['baia_updated_at'])) {
                $baia = Baia::find($horario['baia_id']);

                if (!$baia) {
                    $customErrors["horarios.{$index}.baia_id"] = 'Baia não encontrada.';
                    continue;
                }

                $baiaUpdatedAt = Carbon::parse($horario['baia_updated_at']);
                $currentBaiaUpdatedAt = $baia->updated_at;

                if (!$baiaUpdatedAt->equalTo($currentBaiaUpdatedAt)) {
                    $customErrors["horarios.{$index}.baia_id"] = 'A baia foi modificada por outro usuário. Por favor, recarregue a página e tente novamente.';
                    continue;
                }
            }

            if (!empty($horario['usuario_projeto_id']) && !in_array($oldHorario->tipo, [TipoHorario::TRABALHO_PRESENCIAL, TipoHorario::TRABALHO_REMOTO])) {
                $customErrors["horarios.{$index}.usuario_projeto_id"] = 'O projeto só pode ser informado para horários de trabalho presencial ou remoto.';
            }
            // else if (!empty($horario['baia_id']) && $oldHorario->tipo !== TipoHorario::TRABALHO_PRESENCIAL) {
            //     $customErrors["horarios.{$index}.baia_id"] = 'A baia só pode ser informada para horários de trabalho presencial.';
            // }
        }

        if (!empty($customErrors)) {
            return redirect()->back()->withErrors($customErrors);
        }

        if (!empty($validatedData['horarios'])) {
            DB::transaction(function () use ($validatedData, $request) {
                foreach ($validatedData['horarios'] as $horarioData) {

                    // Se o tipo for ausente ou aula, limpa a baia_id e usuario_projeto_id
                    if (in_array($horarioData['tipo'], [TipoHorario::AUSENTE->value, TipoHorario::EM_AULA->value])) {
                        $horarioData['baia_id'] = null;
                        $horarioData['usuario_projeto_id'] = null;
                    }

                    if ($horarioData['tipo'] === TipoHorario::TRABALHO_REMOTO->value) {
                        $horarioData['baia_id'] = null;
                    }

                    // Implementação adicional de locking otimista no momento do update
                    if (!empty($horarioData['baia_id']) && !empty($horarioData['baia_updated_at'])) {
                        $baia = Baia::lockForUpdate()->find($horarioData['baia_id']);

                        if (!$baia) {
                            throw new \Exception('Baia não encontrada durante o update.');
                        }

                        $baiaUpdatedAt = Carbon::parse($horarioData['baia_updated_at']);

                        if (!$baiaUpdatedAt->equalTo($baia->updated_at)) {
                            throw new \Exception('A baia foi modificada por outro usuário durante o processo. Operação cancelada.');
                        }

                        // Atualiza o updated_at da baia para indicar uso
                        $baia->touch();
                    }

                    $request->user()->horarios()
                        ->where('id', $horarioData['id'])
                        ->update([
                            'tipo' => $horarioData['tipo'],
                            'usuario_projeto_id' => $horarioData['usuario_projeto_id'] ?? null,
                            'baia_id' => $horarioData['baia_id'] ?? null,
                        ]);
                }
            });
        }

        // return redirect()->back()->with('success', 'Horários atualizados com sucesso!');
        return redirect()->route('horarios.index')->with('success', 'Horários atualizados com sucesso!');
    }
}
