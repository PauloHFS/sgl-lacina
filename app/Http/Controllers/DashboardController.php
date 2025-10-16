<?php

namespace App\Http\Controllers;

use App\Enums\StatusCadastro;
use App\Models\Projeto;
use App\Models\User;
use App\Models\UsuarioProjeto;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isCoordenador()) {
            return $this->indexCoordenador();
        }

        $usuarioVinculos = UsuarioProjeto::join('projetos', 'usuario_projeto.projeto_id', '=', 'projetos.id')
            ->select('usuario_projeto.*', 'projetos.nome as projeto_nome', 'projetos.cliente as projeto_cliente')
            ->where('usuario_projeto.usuario_id', $user->id)
            ->get();

        return Inertia::render('Dashboard', [
            'projetos' => $usuarioVinculos,
            'projetosCount' => $user->projetos()->count(),
        ]);
    }

    public function indexCoordenador()
    {
        $projetosCount = Projeto::count();
        $usuariosCount = User::count();
        $solicitacoesPendentes = User::where('status_cadastro', StatusCadastro::PENDENTE)->count();
        $ultimosProjetos = Projeto::orderByDesc('created_at')
            ->take(5)
            ->get(['id', 'nome', 'cliente'])
            ->toArray();

        $projetosAtivos = Auth::user()->projetos()->paginate(10);

        return Inertia::render('DashboardCoordenador', [
            'projetosCount' => $projetosCount,
            'usuariosCount' => $usuariosCount,
            'solicitacoesPendentes' => $solicitacoesPendentes,
            'ultimosProjetos' => $ultimosProjetos,
            'projetosAtivos' => $projetosAtivos,
        ]);
    }
}
