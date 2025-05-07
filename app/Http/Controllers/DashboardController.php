<?php

namespace App\Http\Controllers;

use App\Enums\StatusCadastro;
use App\Models\Projeto;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
  public function index()
  {
    $projetosCount = Projeto::count();
    $usuariosCount = User::count();
    $solicitacoesPendentes = User::where('status_cadastro', StatusCadastro::PENDENTE)->count();
    $ultimosProjetos = Projeto::orderByDesc('created_at')
      ->take(5)
      ->get(['id', 'nome', 'cliente'])
      ->toArray();

    return Inertia::render('Dashboard', [
      'projetosCount' => $projetosCount,
      'usuariosCount' => $usuariosCount,
      'solicitacoesPendentes' => $solicitacoesPendentes,
      'ultimosProjetos' => $ultimosProjetos,
    ]);
  }
}
