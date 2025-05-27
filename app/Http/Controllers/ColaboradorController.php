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
        $usuario = User::with('banco')->findOrFail($id);

        $this->authorize('view', $usuario);
        $can_update_colaborador = $request->user()->can('update', $usuario);

        $vinculos = UsuarioProjeto::with('projeto')
            ->where('usuario_id', $usuario->id)
            ->orderByDesc('data_inicio')
            ->get();

        $statusCadastroView = 'INATIVO';
        $projetosAtuais = collect();
        $vinculoPendente = null;

        $userSystemStatus = $usuario->status_cadastro instanceof StatusCadastro
            ? $usuario->status_cadastro
            : StatusCadastro::tryFrom($usuario->status_cadastro);

        if ($userSystemStatus === StatusCadastro::PENDENTE) {
            $statusCadastroView = 'VINCULO_PENDENTE';
        } elseif ($userSystemStatus === StatusCadastro::ACEITO) {
            $vinculoPendente = $vinculos->first(function ($vinculo) {
                $vinculoStatus = $vinculo->status instanceof StatusVinculoProjeto
                    ? $vinculo->status
                    : StatusVinculoProjeto::tryFrom($vinculo->status);
                return $vinculoStatus === StatusVinculoProjeto::PENDENTE;
            });

            if ($vinculoPendente) {
                $statusCadastroView = 'APROVACAO_PENDENTE';
            } else {
                $vinculosAprovados = $vinculos->filter(function ($vinculo) {
                    $vinculoStatus = $vinculo->status instanceof StatusVinculoProjeto
                        ? $vinculo->status
                        : StatusVinculoProjeto::tryFrom($vinculo->status);
                    return $vinculoStatus === StatusVinculoProjeto::APROVADO;
                });

                if ($vinculosAprovados->isNotEmpty()) {
                    $statusCadastroView = 'ATIVO';
                    $projetosAtuais = $vinculosAprovados->map(fn($v) => $v->projeto)
                        ->filter()
                        ->unique('id')
                        ->values();
                }
            }
        }

        $colaboradorData = $usuario->only([
            'id',
            'name',
            'email',
            'linkedin_url',
            'github_url',
            'figma_url',
            'foto_url',
            'area_atuacao',
            'tecnologias',
            'curriculo_lattes_url',
            'cpf',
            'banco', // Already included if loaded with ->with('banco')
            'conta_bancaria',
            'agencia',
            'banco_id',
            'rg',
            'uf_rg',
            'orgao_emissor_rg', // Added orgao_emissor_rg
            'telefone',
            'genero', // Added genero
            'data_nascimento', // Added data_nascimento
            'cep', // Added cep
            'endereco', // Added endereco
            'numero', // Added numero
            'complemento', // Added complemento
            'bairro', // Added bairro
            'cidade', // Added cidade
            'uf' // Added uf
        ]);

        $colaboradorData['created_at'] = $usuario->created_at?->toIso8601String();
        $colaboradorData['updated_at'] = $usuario->updated_at?->toIso8601String();
        $colaboradorData['status_cadastro'] = $statusCadastroView;
        $colaboradorData['vinculo'] = $vinculoPendente
            ? $vinculoPendente->toArray() // Convert to array if it's a model
            : null;
        $colaboradorData['projetos_atuais'] = $projetosAtuais->map(fn($p) => ['id' => $p->id, 'nome' => $p->nome]);

        $bancos = Banco::orderBy('nome')->get(['id', 'nome', 'codigo']);
        $ufs = ['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'];
        // Assuming Genero enum has a method to get values for frontend
        $generos = collect(Genero::cases())->map(fn($g) => ['value' => $g->value, 'label' => $g->getLabel()])->all(); // Changed to use getLabel()


        return inertia('Colaboradores/Show', [
            'colaborador' => $colaboradorData,
            'bancos' => $bancos,
            'ufs' => $ufs,
            'generos' => $generos,
            'can_update_colaborador' => $can_update_colaborador,
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
