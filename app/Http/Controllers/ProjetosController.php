<?php

namespace App\Http\Controllers;

use App\Models\Projeto;
use App\Models\UsuarioProjeto;
use App\Models\Horario;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Enums\TipoProjeto;
use App\Enums\TipoVinculo;
use App\Enums\Funcao;
use App\Enums\StatusVinculoProjeto;
use App\Enums\TipoHorario;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjetosController extends Controller
{
  public function index()
  {
    $search = request()->input('search', '');
    $tab = request()->input('tab', 'todos');
    $user = Auth::user();

    $projetosQuery = Projeto::query();

    if ($search) {
      $projetosQuery->where(function ($query) use ($search) {
        $query->where('nome', 'ilike', "%{$search}%")
          ->orWhere('cliente', 'ilike', "%{$search}%")
          ->orWhere('tipo', 'ilike', "%{$search}%");
      });
    }

    // Join com usuario_projeto para obter informações do vínculo do usuário logado
    $projetosQuery->leftJoin('usuario_projeto as up_status', function ($join) use ($user) {
      $join->on('projetos.id', '=', 'up_status.projeto_id')
        ->where('up_status.usuario_id', $user->id);
    });

    $selectRawCase = "CASE up_order.status " .
      "WHEN '" . StatusVinculoProjeto::APROVADO->value . "' THEN 1 " .
      "WHEN '" . StatusVinculoProjeto::PENDENTE->value . "' THEN 2 " .
      "WHEN '" . StatusVinculoProjeto::RECUSADO->value . "' THEN 3 " .
      "WHEN '" . StatusVinculoProjeto::ENCERRADO->value . "' THEN 4 " .
      "ELSE 5 END";

    $tabConfigs = [
      'colaborador' => [
        'vinculo' => '!=',
        'tipo' => TipoVinculo::COORDENADOR
      ],
      'coordenador' => [
        'vinculo' => '=',
        'tipo' => TipoVinculo::COORDENADOR
      ]
    ];

    $finalSelectColumns = [
      'projetos.id',
      'projetos.nome',
      'projetos.cliente',
      'projetos.tipo',
      'up_status.status as user_status',
      'up_status.tipo_vinculo as user_tipo_vinculo'
    ];

    if (isset($tabConfigs[$tab])) {
      $config = $tabConfigs[$tab];

      $projetosQuery->whereHas('usuarios', function ($query) use ($user, $config) {
        $query->where('users.id', $user->id)
          ->where('usuario_projeto.tipo_vinculo', $config['vinculo'], $config['tipo']);
      });

      $projetosQuery->leftJoin('usuario_projeto as up_order', function ($join) use ($user, $config) {
        $join->on('projetos.id', '=', 'up_order.projeto_id')
          ->where('up_order.usuario_id', $user->id)
          ->where('up_order.tipo_vinculo', $config['vinculo'], $config['tipo']);
      });

      $finalSelectColumns = array_merge($finalSelectColumns, [
        'up_order.status',
        DB::raw($selectRawCase . " as sort_priority")
      ]);
    }

    if ($tab === 'colaborador' || $tab === 'coordenador') {
      $projetosQuery->orderBy('sort_priority', 'asc')->orderBy('projetos.nome', 'asc');
    } else { // 'todos' tab
      $projetosQuery->orderBy('projetos.nome', 'asc');
    }

    $projetos = $projetosQuery->select($finalSelectColumns)
      ->distinct()
      ->get();

    return Inertia::render('Projetos/Index', [
      'projetos' => $projetos,
      'queryparams' => [
        'search' => $search,
        'tab' => $tab,
      ],
    ]);
  }

  public function show()
  {
    $projeto = Projeto::findOrFail(request()->route('projeto'));

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
      ->get(['users.id', 'users.name']);


    $participantesProjeto = null;
    $temVinculosPendentes = false;

    if (
      $usuarioVinculo &&
      $usuarioVinculo->tipo_vinculo === TipoVinculo::COORDENADOR->value &&
      $usuarioVinculo->status === StatusVinculoProjeto::APROVADO->value
    ) {
      $participantesQuery = $projeto->usuarios()
        ->wherePivot('status', StatusVinculoProjeto::APROVADO->value)
        ->orderBy('name');

      $participantesProjeto = $participantesQuery->paginate(10)->through(function ($user) {
        return [
          'id' => $user->id,
          'name' => $user->name,
          'email' => $user->email,
          'foto_url' => $user->foto_url,
          'funcao' => $user->pivot->funcao,
          'tipo_vinculo' => $user->pivot->tipo_vinculo,
          'data_inicio' => $user->pivot->data_inicio,
        ];
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

    return Inertia::render('Projetos/Show', [
      'projeto' => $projeto,
      'funcoes' => array_column(Funcao::cases(), 'value'),
      'usuarioVinculo' => $usuarioVinculo,
      'vinculosDoUsuarioLogadoNoProjeto' => $vinculosDoUsuarioLogadoNoProjeto,
      'participantesProjeto' => $participantesProjeto,
      'temVinculosPendentes' => $temVinculosPendentes,
      'coordenadoresDoProjeto' => $coordenadoresDoProjeto,
      'horariosDosProjetos' => $horariosDosProjetos,
    ]);
  }

  public function create()
  {
    return Inertia::render('Projetos/Create');
  }

  public function edit(Projeto $projeto)
  {
    $usuarioVinculo = $projeto->getUsuarioVinculo(Auth::user()->id);
    if (!$usuarioVinculo || $usuarioVinculo->tipo_vinculo !== TipoVinculo::COORDENADOR->value || $usuarioVinculo->status !== StatusVinculoProjeto::APROVADO->value) {
      return Redirect::route('projetos.show', $projeto->id)->with('error', 'Você não tem permissão para editar este projeto.');
    }

    return Inertia::render('Projetos/Edit', [
      'projeto' => $projeto,
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
    ]);

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
