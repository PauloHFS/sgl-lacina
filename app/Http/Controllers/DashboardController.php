<?php

namespace App\Http\Controllers;

use App\Models\Projeto;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
  public function index()
  {
    // Busca todos os projetos cadastrados
    $projetos = Projeto::all(['id', 'nome', 'cliente', 'tipo']);

    // Retorna para a página Dashboard passando os projetos
    return Inertia::render('Dashboard', [
      'projetos' => $projetos,
    ]);
  }
}
