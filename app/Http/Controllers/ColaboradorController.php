<?php

namespace App\Http\Controllers;

use App\Enums\Genero;
use App\Enums\StatusCadastro;
use App\Enums\StatusVinculoProjeto;
use App\Enums\TipoVinculo;
use App\Events\CadastroAceito;
use App\Events\CadastroRecusado;
use App\Models\Banco;
use App\Models\Projeto;
use App\Models\User;
use App\Models\UsuarioProjeto;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ColaboradorController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $request->validate([
            'status' => 'sometimes|in:cadastro_pendente,vinculo_pendente,ativos,encerrados',
        ]);

        $usuarios = QueryBuilder::for(User::class)
            ->allowedFilters([
                AllowedFilter::callback('status', function (Builder $query, $value) {
                    if ($value === 'cadastro_pendente') {
                        $query->where('status_cadastro', StatusCadastro::PENDENTE);
                    } elseif ($value === 'vinculo_pendente') {
                        $query->whereIn('id', function ($query) {
                            $query->select('usuario_id')
                                ->from('usuario_projeto')
                                ->where('tipo_vinculo', TipoVinculo::COLABORADOR)
                                ->where('status', StatusVinculoProjeto::PENDENTE);
                        });
                    } elseif ($value === 'ativos') {
                        $query->whereIn('id', function ($query) {
                            $query->select('usuario_id')
                                ->from('usuario_projeto')
                                ->where('tipo_vinculo', TipoVinculo::COLABORADOR)
                                ->where('status', StatusVinculoProjeto::APROVADO);
                        });
                    } elseif ($value === 'encerrados') {
                        $query->where('status_cadastro', StatusCadastro::ACEITO)
                            ->where(function ($queryBuilder) {
                                $queryBuilder->where(function ($qAllInactive) {
                                    $qAllInactive->whereExists(function ($subQueryExists) {
                                        $subQueryExists->select(DB::raw(1))
                                            ->from('usuario_projeto')
                                            ->whereColumn('usuario_projeto.usuario_id', 'users.id');
                                    })->whereNotExists(function ($subQueryNotOtherStatus) {
                                        $subQueryNotOtherStatus->select(DB::raw(1))
                                            ->from('usuario_projeto')
                                            ->whereColumn('usuario_projeto.usuario_id', 'users.id')
                                            ->where('status', '!=', StatusVinculoProjeto::ENCERRADO);
                                    });
                                })->orWhereNotExists(function ($subQuery) {
                                    $subQuery->select(DB::raw(1))
                                        ->from('usuario_projeto')
                                        ->whereColumn('usuario_projeto.usuario_id', 'users.id');
                                });
                            });
                    }
                }),
                AllowedFilter::scope('search'),
            ])
            ->defaultSort('-created_at')
            ->paginate($request->input('per_page', 10))
            ->appends($request->query());

        return Inertia::render('Colaboradores/Index', [
            'colaboradores' => $usuarios,
        ]);
    }

    public function show(Request $request, $id)
    {
        $usuario = User::with(['banco', 'projetos'])->findOrFail($id);

        $this->authorize('view', $usuario);
        $can_update_colaborador = $request->user()->can('update', $usuario);

        $ultimoVinculo = UsuarioProjeto::where('usuario_id', $usuario->id)
            ->orderBy('created_at', 'desc')
            ->with(['projeto'])
            ->first();

        if ($usuario->status_cadastro === StatusCadastro::PENDENTE) {
            $status_colaborador = 'APROVACAO_PENDENTE';
        } elseif ($usuario->projetos->contains(fn ($projeto) => $projeto->vinculo && $projeto->vinculo->status === StatusVinculoProjeto::PENDENTE->value)) {
            $status_colaborador = 'VINCULO_PENDENTE';
        } elseif ($usuario->projetos->contains(fn ($projeto) => $projeto->vinculo && $projeto->vinculo->status === StatusVinculoProjeto::APROVADO->value)) {
            $status_colaborador = 'ATIVO';
        } else {
            $status_colaborador = 'ENCERRADO';
        }

        // Prepare base colaborador data, excluding the original 'projetos' to avoid issues with serialization
        $colaboradorData = collect($usuario->toArray())->except([
            'email_verified_at',
            'password',
            'remember_token',
            'deleted_at',
            'projetos', // Exclude original projetos to rebuild with correct vinculo.id
        ])->all();

        $colaboradorData['created_at'] = $usuario->created_at?->toIso8601String();
        $colaboradorData['updated_at'] = $usuario->updated_at?->toIso8601String();

        // Ensure projetos and their vinculos (pivot data) are correctly formatted
        $colaboradorData['projetos'] = $usuario->projetos->map(function ($projeto) use ($usuario) {
            $vinculoData = null;
            if ($projeto->vinculo) { // $projeto->vinculo is the pivot model instance
                $vinculoData = $projeto->vinculo->toArray(); // This includes the pivot 'id'

                // Format dates safely
                if (isset($vinculoData['data_inicio']) && $projeto->vinculo->data_inicio) {
                    try {
                        $vinculoData['data_inicio'] = ($projeto->vinculo->data_inicio instanceof \Carbon\Carbon)
                            ? $projeto->vinculo->data_inicio->toIso8601String()
                            : \Carbon\Carbon::parse((string) $projeto->vinculo->data_inicio)->toIso8601String();
                    } catch (\Exception $e) {
                        Log::error("Error parsing vinculo data_inicio for user {$usuario->id}, project {$projeto->id}: ".$e->getMessage());
                        $vinculoData['data_inicio'] = is_string($projeto->vinculo->data_inicio) ? (string) $projeto->vinculo->data_inicio : null;
                    }
                } else {
                    $vinculoData['data_inicio'] = null;
                }

                if (isset($vinculoData['data_fim']) && $projeto->vinculo->data_fim) {
                    try {
                        $vinculoData['data_fim'] = ($projeto->vinculo->data_fim instanceof \Carbon\Carbon)
                            ? $projeto->vinculo->data_fim->toIso8601String()
                            : \Carbon\Carbon::parse((string) $projeto->vinculo->data_fim)->toIso8601String();
                    } catch (\Exception $e) {
                        Log::error("Error parsing vinculo data_fim for user {$usuario->id}, project {$projeto->id}: ".$e->getMessage());
                        $vinculoData['data_fim'] = is_string($projeto->vinculo->data_fim) ? (string) $projeto->vinculo->data_fim : null;
                    }
                } else {
                    $vinculoData['data_fim'] = null;
                }

                if (isset($vinculoData['created_at']) && $projeto->vinculo->created_at) {
                    try {
                        $vinculoData['created_at'] = ($projeto->vinculo->created_at instanceof \Carbon\Carbon)
                            ? $projeto->vinculo->created_at->toIso8601String()
                            : \Carbon\Carbon::parse((string) $projeto->vinculo->created_at)->toIso8601String();
                    } catch (\Exception $e) {
                        Log::error("Error parsing vinculo created_at for user {$usuario->id}, project {$projeto->id}: ".$e->getMessage());
                        $vinculoData['created_at'] = is_string($projeto->vinculo->created_at) ? (string) $projeto->vinculo->created_at : null;
                    }
                } else {
                    $vinculoData['created_at'] = null;
                }

                if (isset($vinculoData['updated_at']) && $projeto->vinculo->updated_at) {
                    try {
                        $vinculoData['updated_at'] = ($projeto->vinculo->updated_at instanceof \Carbon\Carbon)
                            ? $projeto->vinculo->updated_at->toIso8601String()
                            : \Carbon\Carbon::parse((string) $projeto->vinculo->updated_at)->toIso8601String();
                    } catch (\Exception $e) {
                        Log::error("Error parsing vinculo updated_at for user {$usuario->id}, project {$projeto->id}: ".$e->getMessage());
                        $vinculoData['updated_at'] = is_string($projeto->vinculo->updated_at) ? (string) $projeto->vinculo->updated_at : null;
                    }
                } else {
                    $vinculoData['updated_at'] = null;
                }
            }

            $projectDetails = collect($projeto->toArray())->except(['pivot', 'vinculo'])->all();

            return array_merge(
                $projectDetails,
                ['vinculo' => $vinculoData]
            );
        })->all();

        $bancos = Banco::orderBy('nome')->get(['id', 'nome', 'codigo']);

        return inertia('Colaboradores/Show', [
            'colaborador' => $colaboradorData,
            'bancos' => $bancos,
            'can_update_colaborador' => $can_update_colaborador,
            'status_colaborador' => $status_colaborador,
            'ultimo_vinculo' => $ultimoVinculo,
        ]);
    }

    public function historico(Request $request, User $colaborador)
    {
        // TODO: Descomenta isso aqui depois
        // $this->authorize('view', $colaborador);

        $historico = $colaborador->historicoUsuarioProjeto()
            ->join('projetos', 'historico_usuario_projeto.projeto_id', '=', 'projetos.id')
            ->whereIn('historico_usuario_projeto.status', [
                StatusVinculoProjeto::APROVADO,
                StatusVinculoProjeto::ENCERRADO,
            ])
            ->with(['projeto'])
            ->orderByDesc('historico_usuario_projeto.data_inicio')
            ->orderBy('projetos.nome')
            ->select('historico_usuario_projeto.*')
            ->paginate(10);

        return inertia('Colaboradores/Historico', [
            'colaborador' => $colaborador,
            'historico' => $historico,
        ]);
    }

    public function update(Request $request, User $colaborador)
    {
        $this->authorize('update', $colaborador);

        $validatedData = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($colaborador->id),
            ],
            'curriculo_lattes_url' => 'nullable|url|max:1000',
            'linkedin_url' => 'nullable|url|max:255',
            'github_url' => 'nullable|url|max:255',
            'website_url' => 'nullable|url|max:255',
            'area_atuacao' => 'nullable|string',
            'tecnologias' => 'nullable|string',
            'campos_extras' => 'nullable|array',
            'cpf' => [
                'nullable',
                'string',
                'regex:/^\d{11}$/', // Validates CPF format (11 digits)
                Rule::unique('users')->ignore($colaborador->id),
            ],
            'rg' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('users')->ignore($colaborador->id),
            ],
            'uf_rg' => 'nullable|string|size:2',
            'orgao_emissor_rg' => 'nullable|string|max:255',
            'telefone' => 'nullable|string|max:255', // Consider a more specific validation (e.g., regex for phone numbers)
            'banco_id' => 'nullable|uuid|exists:bancos,id',
            'conta_bancaria' => 'nullable|string|max:255',
            'agencia' => 'nullable|string|max:255',
            'genero' => ['nullable', Rule::enum(Genero::class)],
            'data_nascimento' => 'nullable|date_format:Y-m-d', // Ensure frontend sends in Y-m-d format
            'cep' => 'nullable|string|regex:/^\d{8}$/', // Validates CEP format (8 digits)
            'endereco' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:255',
            'complemento' => 'nullable|string|max:255',
            'bairro' => 'nullable|string|max:255',
            'cidade' => 'nullable|string|max:255',
            'uf' => 'nullable|string|size:2',
        ])->validate();

        // Handle date conversion for data_nascimento if it's present
        if (isset($validatedData['data_nascimento'])) {
            $validatedData['data_nascimento'] = $validatedData['data_nascimento'] ? \Carbon\Carbon::createFromFormat('Y-m-d', $validatedData['data_nascimento'])->startOfDay() : null;
        }

        try {
            $colaborador->fill($validatedData);
            $colaborador->save();

            return redirect()->route('colaboradores.show', $colaborador->id)->with('success', 'Dados do colaborador atualizados com sucesso.');
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar colaborador: '.$e->getMessage());

            return redirect()->back()->withInput()->with('error', 'Erro ao atualizar os dados do colaborador. Tente novamente.');
        }
    }

    public function aceitar(User $colaborador, Request $request)
    {
        $this->authorize('update', $colaborador);
        $request->validate([
            'observacao' => 'nullable|string|max:1000',
        ]);

        $observacao = $request->input('observacao', '');

        if ($colaborador->status_cadastro === StatusCadastro::PENDENTE) {
            $colaborador->status_cadastro = StatusCadastro::ACEITO;
            $colaborador->save();

            event(new CadastroAceito($colaborador, null, $observacao));

            return redirect()->back()->with('success', 'Cadastro do colaborador aceito com sucesso.');
        }

        return redirect()->back()->with('error', 'Este colaborador não está com cadastro pendente.');
    }

    public function recusar(User $colaborador, Request $request)
    {
        $this->authorize('update', $colaborador);

        if ($colaborador->status_cadastro !== StatusCadastro::PENDENTE) {
            return redirect()->back()->with('error', 'Este colaborador não está com cadastro pendente.');
        }

        $request->validate([
            'observacao' => 'nullable|string|max:1000',
        ]);

        try {
            // Armazenar dados para envio de email antes da deleção
            $dadosColaborador = [
                'name' => $colaborador->name,
                'email' => $colaborador->email,
            ];

            $observacao = $request->input('observacao');

            // Deletar o usuário em vez de marcar como recusado
            $colaborador->delete();

            // Disparar evento com os dados armazenados
            event(new CadastroRecusado($dadosColaborador, null, $observacao));

            return redirect()->route('colaboradores.index', ['status' => 'cadastro_pendente'])
                ->with('success', 'Cadastro do colaborador recusado e removido do sistema com sucesso.');
        } catch (\Exception $e) {
            Log::error('Erro ao recusar colaborador: '.$e->getMessage());

            return redirect()->back()->with('error', 'Erro ao recusar o cadastro. Tente novamente.');
        }
    }
}
