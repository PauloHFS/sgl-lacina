<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UsuarioProjeto;
use App\Models\Projeto;
use App\Enums\TipoVinculo;
use App\Enums\Funcao;
use App\Enums\StatusVinculoProjeto;

class ProjetoVinculoController extends Controller
{
  public function solicitarVinculo(Request $request, Projeto $projeto)
  {
    $user = Auth::user();

    // Verifica se já existe vínculo ativo ou pendente
    $jaSolicitado = UsuarioProjeto::where('usuario_id', $user->id)
      ->where('projeto_id', $projeto->id)
      ->whereNull('data_fim')
      ->whereIn('status', ['PENDENTE', 'APROVADO'])
      ->exists();

    if ($jaSolicitado) {
      return back()->with('error', 'Você já possui solicitação ou vínculo ativo neste projeto.');
    }

    UsuarioProjeto::create([
      'usuario_id' => $user->id,
      'projeto_id' => $projeto->id,
      'tipo_vinculo' => TipoVinculo::COLABORADOR,
      'funcao' => Funcao::ALUNO,
      'status' => StatusVinculoProjeto::PENDENTE,
      'data_inicio' => now(),
    ]);

    return back()->with('success', 'Solicitação de vínculo enviada com sucesso!');
  }
}
