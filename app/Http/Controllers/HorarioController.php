<?php

namespace App\Http\Controllers;

use App\Enums\StatusVinculoProjeto;
use App\Enums\TipoHorario;
use App\Models\Baia;
use App\Models\Horario;
use App\Models\Sala;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
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

            $tipoAtual = $horario['tipo'] ?? $oldHorario->tipo->value;

            if (!empty($horario['usuario_projeto_id']) && !in_array($tipoAtual, [TipoHorario::TRABALHO_PRESENCIAL->value, TipoHorario::TRABALHO_REMOTO->value])) {
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
                    // Busca o horário atual para obter o tipo se não for fornecido
                    $horarioAtual = $request->user()->horarios()->find($horarioData['id']);
                    $tipo = $horarioData['tipo'] ?? $horarioAtual->tipo->value;

                    // Se o tipo for ausente ou aula, limpa a baia_id e usuario_projeto_id
                    if (in_array($tipo, [TipoHorario::AUSENTE->value, TipoHorario::EM_AULA->value])) {
                        $horarioData['baia_id'] = null;
                        $horarioData['usuario_projeto_id'] = null;
                    }

                    if ($tipo === TipoHorario::TRABALHO_REMOTO->value) {
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

                    // Prepara os campos para atualização
                    $updateData = ['tipo' => $tipo];

                    // Se mudou para tipo que limpa campos, força limpeza
                    if (in_array($tipo, [TipoHorario::AUSENTE->value, TipoHorario::EM_AULA->value])) {
                        $updateData['baia_id'] = null;
                        $updateData['usuario_projeto_id'] = null;
                    } elseif ($tipo === TipoHorario::TRABALHO_REMOTO->value) {
                        $updateData['baia_id'] = null;
                        // Só atualiza projeto se foi enviado
                        if (isset($horarioData['usuario_projeto_id'])) {
                            $updateData['usuario_projeto_id'] = $horarioData['usuario_projeto_id'];
                        }
                    } else {
                        // Para outros tipos, só atualiza campos que foram enviados
                        if (isset($horarioData['usuario_projeto_id'])) {
                            $updateData['usuario_projeto_id'] = $horarioData['usuario_projeto_id'];
                        }

                        if (isset($horarioData['baia_id'])) {
                            $updateData['baia_id'] = $horarioData['baia_id'];
                        }
                    }

                    $request->user()->horarios()
                        ->where('id', $horarioData['id'])
                        ->update($updateData);

                    Log::info('Horário atualizado:', [
                        'horario_id' => $horarioData['id'],
                        'update_data' => $updateData,
                        'affected_rows' => $request->user()->horarios()->where('id', $horarioData['id'])->count()
                    ]);
                }
            });
        }

        // return redirect()->back()->with('success', 'Horários atualizados com sucesso!');
        return redirect()->route('horarios.index')->with('success', 'Horários atualizados com sucesso!');
    }

    /**
     * Busca salas e baias disponíveis para um horário específico.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSalasDisponiveis(Request $request)
    {
        $validatedData = $request->validate([
            'dia_da_semana' => 'required|string|in:SEGUNDA,TERCA,QUARTA,QUINTA,SEXTA,SABADO,DOMINGO',
            'horario' => 'required|integer|min:0|max:23',
        ]);

        $diaDaSemana = $validatedData['dia_da_semana'];
        $horario = $validatedData['horario'];

        // Buscar baias ocupadas neste horário
        $baiasOcupadas = Horario::where('dia_da_semana', $diaDaSemana)
            ->where('horario', $horario)
            ->where('tipo', TipoHorario::TRABALHO_PRESENCIAL)
            ->whereNotNull('baia_id')
            ->where('usuario_id', '!=', $request->user()->id) // Exclui o próprio usuário
            ->pluck('baia_id')
            ->toArray();

        // Buscar salas ativas com baias não ocupadas
        $salas = Sala::ativas()
            ->with(['baias' => function ($query) use ($baiasOcupadas) {
                $query->ativas()
                    ->whereNotIn('id', $baiasOcupadas)
                    ->orderBy('nome');
            }])
            ->whereHas('baias', function ($query) use ($baiasOcupadas) {
                $query->ativas()->whereNotIn('id', $baiasOcupadas);
            })
            ->orderBy('nome')
            ->get(['id', 'nome']);

        return response()->json(['salas' => $salas]);
    }

    /**
     * Busca projetos ativos do usuário.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProjetosAtivos(Request $request)
    {
        $projetos = $request->user()->vinculos()
            ->with('projeto:id,nome')
            ->where('status', StatusVinculoProjeto::APROVADO)
            ->whereNull('data_fim')
            ->get()
            ->map(function ($vinculo) {
                return [
                    'id' => $vinculo->id,
                    'projeto_id' => $vinculo->projeto->id,
                    'projeto_nome' => $vinculo->projeto->nome,
                    'carga_horaria' => $vinculo->carga_horaria,
                ];
            });

        return response()->json(['projetos' => $projetos]);
    }

    /**
     * Exibe os horários de um colaborador em um projeto específico.
     *
     * @param  string  $colaboradorId
     * @param  string  $projetoId
     * @return \Inertia\Response
     */
    public function show(string $colaboradorId, string $projetoId)
    {
        // Verificar se o usuário atual tem permissão para ver os horários
        $currentUser = Auth::user();

        // Se não for coordenador, só pode ver seus próprios horários
        if (!$currentUser->isCoordenador() && $currentUser->id !== $colaboradorId) {
            abort(403, 'Você não tem permissão para visualizar estes horários.');
        }

        // Buscar o colaborador
        $colaborador = \App\Models\User::findOrFail($colaboradorId);

        // Buscar o vínculo ativo do colaborador com o projeto
        $vinculo = \App\Models\UsuarioProjeto::where('usuario_id', $colaboradorId)
            ->where('projeto_id', $projetoId)
            ->where('status', StatusVinculoProjeto::APROVADO)
            ->whereNull('data_fim')
            ->with('projeto:id,nome,cliente')
            ->first();

        if (!$vinculo) {
            abort(404, 'Colaborador não possui vínculo ativo com este projeto.');
        }

        // Buscar os horários do colaborador para este projeto
        $horarios = Horario::where('usuario_id', $colaboradorId)
            ->where('usuario_projeto_id', $vinculo->id)
            ->with([
                'baia:id,nome,sala_id',
                'baia.sala:id,nome'
            ])
            ->orderBy('dia_da_semana')
            ->orderBy('horario', 'asc')
            ->get()
            ->groupBy('dia_da_semana');

        // Estruturar dados para o frontend
        $diasSemana = [
            'SEGUNDA' => 'Segunda-feira',
            'TERCA' => 'Terça-feira',
            'QUARTA' => 'Quarta-feira',
            'QUINTA' => 'Quinta-feira',
            'SEXTA' => 'Sexta-feira',
            'SABADO' => 'Sábado',
            'DOMINGO' => 'Domingo'
        ];

        $horariosFormatados = [];
        foreach ($diasSemana as $dia => $diaFormatado) {
            $horariosFormatados[$dia] = [
                'nome' => $diaFormatado,
                'horarios' => $horarios->get($dia, collect())->map(function ($horario) {
                    return [
                        'id' => $horario->id,
                        'horario' => $horario->horario,
                        'tipo' => $horario->tipo,
                        'baia' => $horario->baia ? [
                            'id' => $horario->baia->id,
                            'nome' => $horario->baia->nome,
                            'sala' => $horario->baia->sala ? [
                                'id' => $horario->baia->sala->id,
                                'nome' => $horario->baia->sala->nome
                            ] : null
                        ] : null
                    ];
                })
            ];
        }

        return Inertia::render('Horarios/Show', [
            'colaborador' => [
                'id' => $colaborador->id,
                'name' => $colaborador->name,
                'email' => $colaborador->email
            ],
            'projeto' => [
                'id' => $vinculo->projeto->id,
                'nome' => $vinculo->projeto->nome,
                'cliente' => $vinculo->projeto->cliente
            ],
            'vinculo' => [
                'id' => $vinculo->id,
                'funcao' => $vinculo->funcao,
                'carga_horaria' => $vinculo->carga_horaria,
                'data_inicio' => $vinculo->data_inicio
            ],
            'horarios' => $horariosFormatados,
        ]);
    }
}
