<?php

namespace App\Http\Controllers;

use App\Models\Projeto;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Enums\TipoProjeto;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;

class ProjetosController extends Controller
{
  public function index()
  {
    $projetos = Projeto::all(['id', 'nome', 'cliente', 'tipo']);

    return Inertia::render('Projetos', [
      'projetos' => $projetos,
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

    $projeto = new Projeto($validatedData);
    $projeto->id = Str::uuid();
    $projeto->save();

    return Redirect::route('projetos.index')->with('success', 'Projeto cadastrado com sucesso!');
  }
}
