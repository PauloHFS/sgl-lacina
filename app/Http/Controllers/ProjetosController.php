<?php

namespace App\Http\Controllers;

use App\Models\Projeto;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Enums\TipoProjeto;
use App\Enums\TipoVinculo;
use App\Enums\Funcao;
use App\Enums\StatusVinculoProjeto;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjetosController extends Controller
{
  public function index()
  {
    $projetos = Projeto::all(['id', 'nome', 'cliente', 'tipo']);

    return Inertia::render('Projetos/Index', [
      'projetos' => $projetos,
    ]);
  }

  public function show()
  {
    $projeto = Projeto::findOrFail(request()->route('projeto'));

    if (!$projeto) {
      return Redirect::route('projetos.index')->with('error', 'Projeto não encontrado.');
    }

    $usuarioVinculo = $projeto->getUsuarioVinculo(Auth::user()->id);

    return Inertia::render('Projetos/Show', [
      'projeto' => $projeto,
      'tiposVinculo' => array_column(TipoVinculo::cases(), 'value'),
      'funcoes' => array_column(Funcao::cases(), 'value'),
      'usuarioVinculo' => $usuarioVinculo,
    ]);
  }

  public function create()
  {
    return Inertia::render('Projetos/Create', [
      'tiposProjeto' => array_column(TipoProjeto::cases(), 'value'),
    ]);
  }

  public function store(Request $request)
  {
    $validatedData = $request->validate([
      'nome' => 'required|string|max:255',
      'descricao' => 'nullable|string',
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
          'carga_horaria_semanal' => 0,
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
}
