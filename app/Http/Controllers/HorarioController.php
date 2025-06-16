<?php

namespace App\Http\Controllers;

use App\Enums\TipoHorario;
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
        ], [
            'horarios.*.tipo.in' => 'O tipo de horário deve ser válido.',
            'horarios.*.usuario_projeto_id.exists' => 'O projeto selecionado não existe.',
            'horarios.*.baia_id.exists' => 'A baia selecionada não existe.',
        ]);

        // Validação customizada para garantir que apenas um campo seja preenchido por vez
        $customErrors = [];
        foreach ($validatedData['horarios'] as $index => $horario) {
            $fieldsPresent = collect(['tipo', 'usuario_projeto_id', 'baia_id'])
                ->filter(fn($field) => !empty($horario[$field]))
                ->count();

            if ($fieldsPresent !== 1) {
                $customErrors["horarios.{$index}"] = 'Deve ser informado apenas um campo: tipo, projeto ou baia.';
            }
        }

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

            if (empty($horario['usuario_projeto_id']) && ($oldHorario->tipo == TipoHorario::TRABALHO_PRESENCIAL || $oldHorario->tipo == TipoHorario::TRABALHO_REMOTO)) {
                $customErrors["horarios.{$index}.usuario_projeto_id"] = 'O projeto deve ser informado para horários presenciais ou híbridos.';
            } else if ($horario['baia_id'] && $oldHorario->tipo != TipoHorario::TRABALHO_PRESENCIAL) {
                $customErrors["horarios.{$index}.baia_id"] = 'A baia só pode ser informada para horários presenciais.';
            }
        }

        if (!empty($customErrors)) {
            return redirect()->back()->withErrors($customErrors);
        }

        if (!empty($validatedData['horarios'])) {
            DB::transaction(function () use ($validatedData, $request) {
                foreach ($validatedData['horarios'] as $horarioData) {
                    if (!empty($horarioData['tipo'])) {
                        $request->user()->horarios()
                            ->where('id', $horarioData['id'])
                            ->update([
                                'tipo' => $horarioData['tipo'],
                            ]);
                    } elseif (!empty($horarioData['usuario_projeto_id'])) {
                        $request->user()->horarios()
                            ->where('id', $horarioData['id'])
                            ->update([
                                'usuario_projeto_id' => $horarioData['usuario_projeto_id'],
                            ]);
                    } elseif (!empty($horarioData['baia_id'])) {
                        $request->user()->horarios()
                            ->where('id', $horarioData['id'])
                            ->update([
                                'baia_id' => $horarioData['baia_id'],
                            ]);
                    }
                }
            });
        }

        return redirect()->back()->with('success', 'Horários atualizados com sucesso!');
    }
}

/*
A request vai vir apenas com os horarios que foram alterados
Vai vir o id do horário (todos os horarios de um usuario já estão cadastrados)
Ai deve vir apenas 1 dos seguintes campos:
Pode vir o tipo no do horario (ENUM TipoHorario)
Pode vir o usuario_projeto_id
Pode vir o baia_id
*/