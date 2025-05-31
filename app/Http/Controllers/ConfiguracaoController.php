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
        return Inertia::render('Configuracao/Index', [
            'configuracoes' => [
                'senha_laboratorio_existe' => ConfiguracaoSistema::where('chave', 'senha_laboratorio')->exists(),
            ]
        ]);
    }

    /**
     * Atualiza a senha do laboratório
     */
    public function atualizarSenhaLaboratorio(Request $request): RedirectResponse
    {
        $request->validate([
            'senha_atual' => 'required|string',
            'nova_senha' => 'required|string|min:4|confirmed',
        ], [
            'senha_atual.required' => 'A senha atual é obrigatória.',
            'nova_senha.required' => 'A nova senha é obrigatória.',
            'nova_senha.min' => 'A nova senha deve ter pelo menos 4 caracteres.',
            'nova_senha.confirmed' => 'A confirmação da nova senha não confere.',
        ]);

        $senhaAtual = ConfiguracaoSistema::obterValor('senha_laboratorio');

        if (!Hash::check($request->senha_atual, $senhaAtual)) {
            return back()->withErrors(['senha_atual' => 'A senha atual está incorreta.']);
        }

        ConfiguracaoSistema::definirValor(
            'senha_laboratorio',
            Hash::make($request->nova_senha),
            'Senha para cadastro no laboratório'
        );

        return back()->with('success', 'Senha do laboratório atualizada com sucesso!');
    }
}
