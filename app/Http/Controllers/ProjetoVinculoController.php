<?php

namespace App\Http\Controllers;

use App\Enums\Funcao;
use App\Enums\StatusCadastro;
use App\Enums\StatusVinculoProjeto;
use App\Enums\TipoVinculo;
use App\Events\SolicitacaoVinculoCriada;
use App\Events\VinculoAceito;
use App\Models\Projeto;
use App\Models\UsuarioProjeto;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProjetoVinculoController extends Controller
{
    public function create(Request $request)
    {
        $projeto = Projeto::find($request->input('projeto_id'));

        $dataInicioRules = ['required', 'date'];

        if ($projeto) {
            $dataInicioRules[] = 'after_or_equal:'.$projeto->data_inicio;
            $dataInicioRules[] = 'before:'.$projeto->data_termino;
        }

        $request->validate([
            'projeto_id' => 'required|exists:projetos,id',
            'data_inicio' => $dataInicioRules,
            'carga_horaria' => 'required|integer|min:1|max:200',
            'valor_bolsa' => 'sometimes|nullable|integer|min:0',
            'tipo_vinculo' => ['required', Rule::enum(TipoVinculo::class)],
            'funcao' => ['required', Rule::enum(Funcao::class)],
            'trocar' => 'sometimes|boolean',
            'usuario_projeto_trocado_id' => 'sometimes|exists:usuario_projeto,id',
        ]);

        $user = Auth::user();

        if ($user->status_cadastro !== StatusCadastro::ACEITO) {
            return back()->with('error', 'Seu cadastro não está aceito. Entre em contato com o administrador.');
        }

        $jaSolicitado = UsuarioProjeto::where('usuario_id', $user->id)
            ->where('projeto_id', $request->projeto_id)
            ->whereNull('data_fim')
            ->whereIn('status', ['PENDENTE', 'APROVADO'])
            ->exists();

        if ($jaSolicitado) {
            return back()->with('error', 'Você já possui solicitação ou vínculo ativo neste projeto.');
        }

        if ($request->trocar) {
            // checar se ja nao tem um trocar true para esse usuario
            $jaTemTrocar = UsuarioProjeto::where('usuario_id', $user->id)->where('trocar', true)->exists();

            if ($jaTemTrocar) {
                return back()->with('error', 'Você já possui uma troca em andamento.');
            }

            $novoVinculo = DB::transaction(function () use ($request, $user) {
                UsuarioProjeto::whereId($request->usuario_projeto_trocado_id)
                    ->update([
                        'trocar' => true,
                    ]);

                $novoVinculo = UsuarioProjeto::create([
                    'usuario_id' => $user->id,
                    'projeto_id' => $request->projeto_id,
                    'tipo_vinculo' => $request->tipo_vinculo,
                    'funcao' => $request->funcao,
                    'status' => StatusVinculoProjeto::PENDENTE,
                    'carga_horaria' => $request->carga_horaria,
                    'valor_bolsa' => $request->valor_bolsa ?? 0,
                    'data_inicio' => $request->data_inicio,
                ]);

                return $novoVinculo;
            });
        } else {
            $novoVinculo = UsuarioProjeto::create([
                'usuario_id' => $user->id,
                'projeto_id' => $request->projeto_id,
                'tipo_vinculo' => $request->tipo_vinculo,
                'funcao' => $request->funcao,
                'status' => StatusVinculoProjeto::PENDENTE,
                'carga_horaria' => $request->carga_horaria,
                'valor_bolsa' => $request->valor_bolsa ?? 0,
                'data_inicio' => $request->data_inicio,
            ]);
        }

        event(new SolicitacaoVinculoCriada($novoVinculo));

        return back()->with('success', 'Solicitação de vínculo enviada com sucesso!');
    }

    public function update(Request $request, $id)
    {
        $usuarioProjeto = UsuarioProjeto::with('usuario')->findOrFail($id);

        $validatedData = $request->validate([
            'status' => ['sometimes', 'required', Rule::enum(StatusVinculoProjeto::class)],
            'carga_horaria' => 'sometimes|nullable|integer|min:1|max:200',
            'valor_bolsa' => 'sometimes|nullable|integer|min:0',
            'funcao' => ['sometimes', 'nullable', Rule::enum(Funcao::class)],
            'tipo_vinculo' => ['sometimes', 'nullable', Rule::enum(TipoVinculo::class)],
            'data_inicio' => 'sometimes|nullable|date',
            'data_fim' => [
                'sometimes',
                'nullable',
                'date',
                function ($attribute, $value, $fail) use ($request, $usuarioProjeto) {
                    if ($value) {
                        $dataInicio = $request->input('data_inicio', $usuarioProjeto->data_inicio);

                        // Convert both dates to Carbon instances for proper comparison (date only)
                        $dataInicioCarbon = Carbon::parse($dataInicio)->startOfDay();
                        $dataFimCarbon = Carbon::parse($value)->startOfDay();

                        if ($dataFimCarbon->lt($dataInicioCarbon)) {
                            $fail('A data de término deve ser posterior ou igual à data de início.');
                        }
                    }
                },
            ],
            'skip_history' => 'sometimes|boolean',
        ]);

        $statusOriginal = $usuarioProjeto->status;
        $vinculoFoiAceito = false;

        if ($request->filled('status')) {
            $usuarioProjeto->status = $validatedData['status'];

            if (
                $statusOriginal !== StatusVinculoProjeto::APROVADO &&
                $usuarioProjeto->status === StatusVinculoProjeto::APROVADO
            ) {
                $vinculoFoiAceito = true;

                $vinculoAntigo = UsuarioProjeto::where('trocar', true)->where('usuario_id', $usuarioProjeto->usuario_id)->first();
                if ($vinculoAntigo) {
                    $vinculoAntigo->trocar = false;
                    $vinculoAntigo->status = StatusVinculoProjeto::ENCERRADO;
                    $vinculoAntigo->data_fim = $usuarioProjeto->data_inicio;
                    $vinculoAntigo->save();
                }
            }

            if ($validatedData['status'] === StatusVinculoProjeto::ENCERRADO && ! $request->filled('data_fim')) {
                $usuarioProjeto->data_fim = now();
            } elseif ($validatedData['status'] !== StatusVinculoProjeto::ENCERRADO) {
                // Se o status não for ENCERRADO, garantir que data_fim seja null,
                // a menos que explicitamente fornecido e diferente de ENCERRADO (cenário incomum).
                if (! $request->filled('data_fim')) {
                    $usuarioProjeto->data_fim = null;
                }
            }
        }

        if ($request->filled('carga_horaria')) {
            $usuarioProjeto->carga_horaria = $validatedData['carga_horaria'];
        }

        if ($request->has('valor_bolsa')) {
            $usuarioProjeto->valor_bolsa = $validatedData['valor_bolsa'];
        }

        if ($request->filled('funcao')) {
            $usuarioProjeto->funcao = $validatedData['funcao'];
        }

        if ($request->filled('tipo_vinculo')) {
            $usuarioProjeto->tipo_vinculo = $validatedData['tipo_vinculo'];
        }

        if ($request->filled('data_inicio')) {
            $usuarioProjeto->data_inicio = $validatedData['data_inicio'];
        }

        if ($request->has('data_fim')) {
            $usuarioProjeto->data_fim = $validatedData['data_fim'];
        }

        $usuarioProjeto->skipHistoryLog = $request->boolean('skip_history', false);
        $usuarioProjeto->save();

        if ($vinculoFoiAceito) {
            event(new VinculoAceito($usuarioProjeto->usuario, $usuarioProjeto->projeto));
        }

        return back()->with('success', 'Vínculo com projeto atualizado com sucesso!');
    }
}
