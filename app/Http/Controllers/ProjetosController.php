<?php

namespace App\Http\Controllers;

use App\Models\Projeto;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProjetosController extends Controller
{
  public function index()
  {
    $projetos = Projeto::all(['id', 'nome', 'cliente', 'tipo']); // Refatora isso para ser paginado

    return Inertia::render('Projetos', [
      'projetos' => $projetos,
    ]);
  }
}
