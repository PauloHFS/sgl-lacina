<?php

namespace App\Http\Controllers;

use App\Models\Projeto;
use App\Models\UsuarioProjeto;
use App\Models\Horario;
use App\Models\DailyReport;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Enums\TipoProjeto;
use App\Enums\TipoVinculo;
use App\Enums\Funcao;
use App\Enums\StatusVinculoProjeto;
use App\Enums\TipoHorario;
use App\Models\IntervenienteFinanceiro;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ProjetosController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $projetos = QueryBuilder::for(Projeto::class)
            ->allowedFilters([
                AllowedFilter::scope('search'),
                AllowedFilter::callback('tab', function (Builder $query, $value) use ($user) {
                    if ($value === 'colaborador') {
                        $query->whereHas('usuarios', function ($query) use ($user) {
                            $query->where('users.id', $user->id)
                                ->where('usuario_projeto.tipo_vinculo', '!=', TipoVinculo::COORDENADOR->value);
                        });
                    } elseif ($value === 'coordenador') {
                        $query->whereHas('usuarios', function ($query) use ($user) {
                            $query->where('users.id', $user->id)
                                ->where('usuario_projeto.tipo_vinculo', '=', TipoVinculo::COORDENADOR->value);
                        });
                    }
                }),
            ])
            ->with(['usuarios' => function ($query) use ($user) {
                $query->where('users.id', $user->id);
            }])
            ->defaultSort('nome')
            ->paginate($request->input('per_page', 10))
            ->appends($request->query());

        return Inertia::render('Projetos/Index', [
            'projetos' => $projetos,
        ]);
    }

  public function show()
  {
    $projeto = Projeto::with('intervenienteFinanceiro')->findOrFail(request()->route('projeto'));

    if (!$projeto) {
      return Redirect::route('projetos.index')->with('error', 'Projeto não encontrado.');
    }

    $usuarioAutenticado = Auth::user();
    $usuarioVinculo = $projeto->getUsuarioVinculo($usuarioAutenticado->id);

    if (
      !$usuarioVinculo ||
      $usuarioVinculo->tipo_vinculo !== TipoVinculo::COORDENADOR->value ||
      $usuarioVinculo->status !== StatusVinculoProjeto::APROVADO->value
    ) {
      $projeto->makeHidden(['campos_extras', 'meses_execucao', 'valor_total']);
    }

    $vinculosDoUsuarioLogadoNoProjeto = UsuarioProjeto::where('usuario_id', $usuarioAutenticado->id)
      ->with('projeto') // Eager load project data
      ->orderBy('data_inicio', 'desc')
      ->get();

    $coordenadoresDoProjeto = $projeto->usuarios()
      ->wherePivot('tipo_vinculo', TipoVinculo::COORDENADOR->value)
      ->wherePivot('status', StatusVinculoProjeto::APROVADO->value)
      ->orderBy('name')
      ->get(['users.id', 'users.name', 'users.foto_url']);

    $participantesProjeto = null;
    $temVinculosPendentes = false;

    if (
      $usuarioVinculo &&
      $usuarioVinculo->status === StatusVinculoProjeto::APROVADO->value
    ) {
      $isCoordenador = $usuarioVinculo->tipo_vinculo === TipoVinculo::COORDENADOR->value;
      $participantesQuery = $projeto->usuarios()
        ->wherePivot('status', StatusVinculoProjeto::APROVADO->value)
        ->orderBy('name');

      $participantesProjeto = $participantesQuery->paginate(10)->through(function ($user) use ($isCoordenador) {
        $base = [
          'id' => $user->id,
          'name' => $user->name,
          'email' => $user->email,
          'foto_url' => $user->foto_url,
          'funcao' => $user->pivot->funcao,
          'tipo_vinculo' => $user->pivot->tipo_vinculo,
        ];
        if ($isCoordenador) {
          $base['data_inicio'] = $user->pivot->data_inicio;
          $base['data_fim'] = $user->pivot->data_fim;
          $base['carga_horaria'] = $user->pivot->carga_horaria;
          $base['valor_bolsa'] = $user->pivot->valor_bolsa;
        }
        return $base;
      });

      $temVinculosPendentes = $projeto->usuarios()
        ->wherePivot('status', StatusVinculoProjeto::PENDENTE)
        ->exists();
    }

    // Buscar horários dos colaboradores do projeto (somente trabalho presencial e remoto)
    $horariosDosProjetos = null;
    if (
      $usuarioVinculo &&
      (($usuarioVinculo->tipo_vinculo === TipoVinculo::COORDENADOR->value &&
        $usuarioVinculo->status === StatusVinculoProjeto::APROVADO->value) ||
        $usuarioVinculo->status === StatusVinculoProjeto::APROVADO->value)
    ) {

      $horariosDosProjetos = Horario::query()
        ->whereHas('usuarioProjeto', function ($query) use ($projeto) {
          $query->where('projeto_id', $projeto->id)
            ->where('status', StatusVinculoProjeto::APROVADO);
        })
        ->whereIn('tipo', [TipoHorario::TRABALHO_PRESENCIAL, TipoHorario::TRABALHO_REMOTO])
        ->with([
          'usuario:id,name,email,foto_url',
          'baia:id,nome,sala_id',
          'baia.sala:id,nome'
        ])
        ->orderBy('dia_da_semana')
        ->orderBy('horario')
        ->get()
        ->groupBy('dia_da_semana');
    }

    // Daily Reports - Recebe o parâmetro 'dia' (formato: Y-m-d)
    $diaDaily = request()->input('dia');
    $dailyReports = null;
    $totalParticipantes = 0;
    if ($diaDaily) {
      // Buscar todos os participantes aprovados do projeto
      $participantesIds = $projeto->usuarios()
        ->wherePivot('status', StatusVinculoProjeto::APROVADO->value)
        ->pluck('users.id');
      $totalParticipantes = $participantesIds->count();

      // Buscar dailys desse projeto para o dia informado
      $dailyReports = DailyReport::whereIn('usuario_id', $participantesIds)
        ->where('projeto_id', $projeto->id)
        ->with('usuario')
        ->whereDate('data', $diaDaily)
        ->get();
    }

    $canViewAusencias = $usuarioAutenticado->can('viewAusencias', $projeto);
    $ausencias = null;
    if ($canViewAusencias) {
        $participantesIds = $projeto->usuarios()->pluck('users.id');
        $ausencias = \App\Models\Ausencia::whereIn('usuario_id', $participantesIds)
            ->whereIn('status', [\App\Enums\StatusAusencia::APROVADO, \App\Enums\StatusAusencia::PENDENTE])
            ->with('usuario:id,name')
            ->orderByRaw("CASE status WHEN 'PENDENTE' THEN 1 WHEN 'APROVADO' THEN 2 ELSE 3 END")
            ->orderBy('data_inicio', 'desc')
            ->get();
    }

    return Inertia::render('Projetos/Show', [
      'projeto' => $projeto,
      'funcoes' => array_column(Funcao::cases(), 'value'),
      'usuarioVinculo' => $usuarioVinculo,
      'vinculosDoUsuarioLogadoNoProjeto' => $vinculosDoUsuarioLogadoNoProjeto,
      'participantesProjeto' => $participantesProjeto,
      'temVinculosPendentes' => $temVinculosPendentes,
      'coordenadoresDoProjeto' => $coordenadoresDoProjeto,
      'horariosDosProjetos' => $horariosDosProjetos,
      'diaDaily' => $diaDaily,
      'dailyReports' => $dailyReports,
      'totalParticipantes' => $totalParticipantes,
      'canViewAusencias' => $canViewAusencias,
      'ausencias' => $ausencias,
    ]);
  }

  public function create()
  {
    $interveniente = IntervenienteFinanceiro::all();

    return Inertia::render('Projetos/Create', [
      'intervenientes_financeiros' => $interveniente,
    ]);
  }

  public function edit(Projeto $projeto)
  {
    $usuarioVinculo = $projeto->getUsuarioVinculo(Auth::user()->id);
    if (!$usuarioVinculo || $usuarioVinculo->tipo_vinculo !== TipoVinculo::COORDENADOR->value || $usuarioVinculo->status !== StatusVinculoProjeto::APROVADO->value) {
      return Redirect::route('projetos.show', $projeto->id)->with('error', 'Você não tem permissão para editar este projeto.');
    }

    $interveniente = IntervenienteFinanceiro::all();

    return Inertia::render('Projetos/Edit', [
      'projeto' => $projeto,
      'intervenientes_financeiros' => $interveniente,
    ]);
  }

  public function store(Request $request)
  {
    $validatedData = $request->validate([
      'nome' => 'required|string|max:255',
      'descricao' => 'nullable|string|max:2000',
      'valor_total' => 'sometimes|integer|min:0',
      'meses_execucao' => 'sometimes|numeric|min:0',
      'campos_extras' => 'sometimes|array',
      'campos_extras.*' => 'string|max:255', // Validar cada campo extra como string
      'data_inicio' => 'required|date',
      'data_termino' => 'nullable|date|after_or_equal:data_inicio',
      'cliente' => 'required|string|max:255',
      'slack_url' => 'nullable|url|max:255',
      'discord_url' => 'nullable|url|max:255',
      'board_url' => 'nullable|url|max:255',
      'git_url' => 'nullable|url|max:255',
      'tipo' => ['required', new \Illuminate\Validation\Rules\Enum(TipoProjeto::class)],
      'interveniente_financeiro_id' => 'nullable|exists:intervenientes_financeiros,id',
      'numero_convenio' => 'nullable|string|max:255',
    ]);

    if (isset($validatedData['interveniente_financeiro_id']) && $validatedData['interveniente_financeiro_id'] === '') {
      $validatedData['interveniente_financeiro_id'] = null;
    }

    try {
      DB::transaction(function () use ($validatedData) {
        $projeto = new Projeto($validatedData);
        $projeto->id = Str::uuid();
        $projeto->save();

        $projeto->usuarios()->attach(Auth::user()->id, [
          'id' => Str::uuid(),
          'tipo_vinculo' => TipoVinculo::COORDENADOR,
          'funcao' => Funcao::COORDENADOR,
          'status' => StatusVinculoProjeto::APROVADO,
          'carga_horaria' => 0,
          'data_inicio' => now(),
        ]);
      });
    } catch (\Throwable $th) {
      Log::error('Erro ao cadastrar projeto:', [
        'error' => $th->getMessage(),
        'stack' => $th->getTraceAsString(),
      ]);
      return Redirect::route('projetos.index')->with('error', 'Erro ao cadastrar o projeto. Tente novamente mais tarde.');
    }

    return Redirect::route('projetos.index')->with('success', 'Projeto cadastrado com sucesso!');
  }

  public function update(Request $request, Projeto $projeto)
  {
    // Authorization: Check if the authenticated user is a coordinator of this project
    $usuarioVinculo = $projeto->getUsuarioVinculo(Auth::user()->id);
    if (!$usuarioVinculo || $usuarioVinculo->tipo_vinculo !== TipoVinculo::COORDENADOR->value || $usuarioVinculo->status !== StatusVinculoProjeto::APROVADO->value) {
      return Redirect::route('projetos.show', $projeto->id)->with('error', 'Você não tem permissão para editar este projeto.');
    }

    $validatedData = $request->validate([
      'nome' => 'required|string|max:255',
      'descricao' => 'nullable|string',
      'valor_total' => 'sometimes|integer|min:0',
      'meses_execucao' => 'sometimes|numeric|min:0',
      'campos_extras' => 'sometimes|array',
      'campos_extras.*' => 'string|max:255', // Validar cada campo extra como string
      'data_inicio' => 'required|date',
      'data_termino' => 'nullable|date|after_or_equal:data_inicio',
      'cliente' => 'required|string|max:255',
      'slack_url' => 'nullable|url|max:255',
      'discord_url' => 'nullable|url|max:255',
      'board_url' => 'nullable|url|max:255',
      'git_url' => 'nullable|url|max:255',
      'tipo' => ['required', new \Illuminate\Validation\Rules\Enum(TipoProjeto::class)],
      'interveniente_financeiro_id' => 'nullable|exists:intervenientes_financeiros,id',
      'numero_convenio' => 'nullable|string|max:255',
    ]);

    // Convert empty string to null for UUID foreign key fields
    if (isset($validatedData['interveniente_financeiro_id']) && $validatedData['interveniente_financeiro_id'] === '') {
      $validatedData['interveniente_financeiro_id'] = null;
    }

    Log::info('Atualizando projeto:', [
      'projeto_id' => $projeto->id,
      'dados' => $validatedData,
    ]);

    try {
      $projeto->update($validatedData);
    } catch (\Throwable $th) {
      Log::error('Erro ao atualizar projeto:', [
        'error' => $th->getMessage(),
        'stack' => $th->getTraceAsString(),
        'projeto_id' => $projeto->id,
      ]);
      return Redirect::route('projetos.show', $projeto->id)->with('error', 'Erro ao atualizar o projeto. Tente novamente mais tarde.');
    }

    return Redirect::route('projetos.show', $projeto->id)->with('success', 'Projeto atualizado com sucesso!');
  }
}
