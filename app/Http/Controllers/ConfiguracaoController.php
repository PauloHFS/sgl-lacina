<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracaoSistema;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
        $token = null;

        if ($senhaExiste) {
            $token = ConfiguracaoSistema::obterValor('senha_laboratorio');
        }

        return Inertia::render('Configuracao/Index', [
            'configuracoes' => [
                'senha_laboratorio_existe' => $senhaExiste,
                'token_laboratorio' => $token,
            ]
        ]);
    }

    /**
     * Atualiza a senha do laboratório
     */
    public function atualizarSenhaLaboratorio(Request $request): RedirectResponse
    {
        $request->validate([
            'novo_token' => 'required|string|min:4',
        ], [
            'novo_token.required' => 'O novo token é obrigatório.',
            'novo_token.min' => 'O token deve ter pelo menos 4 caracteres.',
        ]);

        ConfiguracaoSistema::definirValor(
            'senha_laboratorio',
            $request->novo_token,
            'Token para cadastro no laboratório'
        );

        return back()->with('success', 'Token do laboratório atualizado com sucesso!');
    }
}
