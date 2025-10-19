<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracaoSistema;
use App\Models\OrgaoEmissor; // Import the model
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ConfiguracaoController extends Controller
{
    /**
     * Exibe a página de configurações
     */
    public function index(): Response
    {
        $senhaExiste = ConfiguracaoSistema::where('chave', 'senha_laboratorio')->exists();
        $senha = null;

        if ($senhaExiste) {
            $senha = ConfiguracaoSistema::obterValor('senha_laboratorio');
        }

        return Inertia::render('Configuracao/Index', [
            'configuracoes' => [
                'senha_laboratorio_existe' => $senhaExiste,
                'senha_laboratorio' => $senha,
            ],
            'orgaosEmissores' => OrgaoEmissor::all(),
        ]);
    }

    /**
     * Atualiza a senha do laboratório
     */
    public function atualizarSenhaLaboratorio(Request $request): RedirectResponse
    {
        $request->validate([
            'novo_senha' => 'required|string|min:4',
        ], [
            'novo_senha.required' => 'O novo senha é obrigatório.',
            'novo_senha.min' => 'O senha deve ter pelo menos 4 caracteres.',
        ]);

        ConfiguracaoSistema::definirValor(
            'senha_laboratorio',
            $request->novo_senha,
            'Senha para cadastro no laboratório'
        );

        return back()->with('success', 'Senha do laboratório atualizado com sucesso!');
    }
}
