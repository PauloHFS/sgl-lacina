<?php

namespace App\Http\Controllers;

use App\Enums\StatusCadastro;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Projeto;
use App\Models\Banco;
use Inertia\Inertia;
use App\Enums\TipoVinculo;
use App\Events\CadastroAceito;
use App\Enums\StatusVinculoProjeto;
use App\Models\UsuarioProjeto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;
use App\Enums\Genero;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ColaboradorController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $request->validate([
            'status' => 'required|in:cadastro_pendente,vinculo_pendente,ativos,encerrados',
        ]);

        $status = $request->input('status');

        $usuarios = null;

        if ($status == 'cadastro_pendente') {
            $usuarios = User::where('status_cadastro', StatusCadastro::PENDENTE)->paginate(10);
        } else if ($status == 'vinculo_pendente') {
            $usuarios = User::whereIn('id', function ($query) {
                $query->select('usuario_id')
                    ->from('usuario_projeto')
                    ->where('tipo_vinculo', TipoVinculo::COLABORADOR)
                    ->where('status', StatusVinculoProjeto::PENDENTE);
            })->paginate(10);
        } else if ($status == 'ativos') {
            $usuarios = User::whereIn('id', function ($query) {
                $query->select('usuario_id')
                    ->from('usuario_projeto')
                    ->where('tipo_vinculo', TipoVinculo::COLABORADOR)
                    ->where('status', StatusVinculoProjeto::APROVADO);
            })->paginate(10);
        } else if ($status == 'encerrados') {
            $usuarios = User::query()
                ->where('status_cadastro', StatusCadastro::ACEITO) // usuários com cadastro aceito
                ->where(function ($queryBuilder) {
                    // Condição 1: Usuários que possuem vínculos, e TODOS esses vínculos em 'usuario_projeto' estão com status ENCERRADO
                    $queryBuilder->where(function ($qAllInactive) {
                        // Sub-condição 1.1: O usuário DEVE ter pelo menos um vínculo.
                        $qAllInactive->whereExists(function ($subQueryExists) {
                            $subQueryExists->select(DB::raw(1))
                                ->from('usuario_projeto')
                                ->whereColumn('usuario_projeto.usuario_id', 'users.id');
                        });
                        // Sub-condição 1.2: E NÃO DEVE existir nenhum vínculo para este usuário com status DIFERENTE de ENCERRADO.
                        $qAllInactive->whereNotExists(function ($subQueryNotOtherStatus) {
                            $subQueryNotOtherStatus->select(DB::raw(1))
                                ->from('usuario_projeto')
                                ->whereColumn('usuario_projeto.usuario_id', 'users.id')
                                ->where('status', '!=', StatusVinculoProjeto::ENCERRADO);
                        });
                    });

                    // Condição 2: OU usuários que não possuem nenhum vínculo na tabela 'usuario_projeto'
                    $queryBuilder->orWhereNotExists(function ($subQuery) {
                        $subQuery->select(DB::raw(1))
                            ->from('usuario_projeto')
                            ->whereColumn('usuario_projeto.usuario_id', 'users.id');
                    });
                })
                ->paginate(10);
        }

        if ($usuarios) {
            return Inertia::render('Colaboradores/Index', [
                'colaboradores' => $usuarios,
            ]);
        }
        return Inertia::render('Colaboradores/Index', []);
    }

    public function show(Request $request, $id)
    {
        $usuario = User::with(['banco', 'projetos'])->findOrFail($id);

        $this->authorize('view', $usuario);
        $can_update_colaborador = $request->user()->can('update', $usuario);

        $ultimoVinculo = UsuarioProjeto::where('usuario_id', $usuario->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($usuario->status_cadastro === StatusCadastro::PENDENTE) {
            $status_colaborador = 'APROVACAO_PENDENTE';
        } elseif ($usuario->projetos->contains(fn($projeto) => $projeto->vinculo && $projeto->vinculo->status === StatusVinculoProjeto::PENDENTE->value)) {
            $status_colaborador = 'VINCULO_PENDENTE';
        } elseif ($usuario->projetos->contains(fn($projeto) => $projeto->vinculo && $projeto->vinculo->status === StatusVinculoProjeto::APROVADO->value)) {
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
            'projetos' // Exclude original projetos to rebuild with correct vinculo.id
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
                            : \Carbon\Carbon::parse((string)$projeto->vinculo->data_inicio)->toIso8601String();
                    } catch (\Exception $e) {
                        Log::error("Error parsing vinculo data_inicio for user {$usuario->id}, project {$projeto->id}: " . $e->getMessage());
                        $vinculoData['data_inicio'] = is_string($projeto->vinculo->data_inicio) ? (string)$projeto->vinculo->data_inicio : null;
                    }
                } else {
                    $vinculoData['data_inicio'] = null;
                }

                if (isset($vinculoData['data_fim']) && $projeto->vinculo->data_fim) {
                    try {
                        $vinculoData['data_fim'] = ($projeto->vinculo->data_fim instanceof \Carbon\Carbon)
                            ? $projeto->vinculo->data_fim->toIso8601String()
                            : \Carbon\Carbon::parse((string)$projeto->vinculo->data_fim)->toIso8601String();
                    } catch (\Exception $e) {
                        Log::error("Error parsing vinculo data_fim for user {$usuario->id}, project {$projeto->id}: " . $e->getMessage());
                        $vinculoData['data_fim'] = is_string($projeto->vinculo->data_fim) ? (string)$projeto->vinculo->data_fim : null;
                    }
                } else {
                    $vinculoData['data_fim'] = null;
                }

                if (isset($vinculoData['created_at']) && $projeto->vinculo->created_at) {
                    try {
                        $vinculoData['created_at'] = ($projeto->vinculo->created_at instanceof \Carbon\Carbon)
                            ? $projeto->vinculo->created_at->toIso8601String()
                            : \Carbon\Carbon::parse((string)$projeto->vinculo->created_at)->toIso8601String();
                    } catch (\Exception $e) {
                        Log::error("Error parsing vinculo created_at for user {$usuario->id}, project {$projeto->id}: " . $e->getMessage());
                        $vinculoData['created_at'] = is_string($projeto->vinculo->created_at) ? (string)$projeto->vinculo->created_at : null;
                    }
                } else {
                    $vinculoData['created_at'] = null;
                }

                if (isset($vinculoData['updated_at']) && $projeto->vinculo->updated_at) {
                    try {
                        $vinculoData['updated_at'] = ($projeto->vinculo->updated_at instanceof \Carbon\Carbon)
                            ? $projeto->vinculo->updated_at->toIso8601String()
                            : \Carbon\Carbon::parse((string)$projeto->vinculo->updated_at)->toIso8601String();
                    } catch (\Exception $e) {
                        Log::error("Error parsing vinculo updated_at for user {$usuario->id}, project {$projeto->id}: " . $e->getMessage());
                        $vinculoData['updated_at'] = is_string($projeto->vinculo->updated_at) ? (string)$projeto->vinculo->updated_at : null;
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
            'figma_url' => 'nullable|url|max:255',
            'area_atuacao' => 'nullable|string',
            'tecnologias' => 'nullable|string',
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
            Log::error("Erro ao atualizar colaborador: " . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Erro ao atualizar os dados do colaborador. Tente novamente.');
        }
    }

    public function aceitar(User $colaborador, Request $request)
    {
        $this->authorize('update', $colaborador);

        if ($colaborador->status_cadastro === StatusCadastro::PENDENTE) {
            $colaborador->status_cadastro = StatusCadastro::ACEITO;
            $colaborador->save();

            event(new CadastroAceito($colaborador));

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

        $colaborador->status_cadastro = 'RECUSADO';
        $colaborador->save();

        // Aqui você pode disparar eventos/emails se necessário

        return redirect()->back()->with('success', 'Colaborador recusado com sucesso.');
    }
}
