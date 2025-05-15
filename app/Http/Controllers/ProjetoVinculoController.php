<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UsuarioProjeto;
use App\Enums\StatusVinculoProjeto;
use App\Enums\TipoVinculo;
use App\Enums\Funcao;
use Illuminate\Validation\Rule;

class ProjetoVinculoController extends Controller
{
  public function create(Request $request)
  {
    // TODO dá um jeito de user o enum aqui no validate
    $request->validate([
      'projeto_id' => 'required|exists:projetos,id',
      'data_inicio' => 'required|date',
      'carga_horaria_semanal' => 'required|integer|min:1|max:40',
      'tipo_vinculo' => ['required', Rule::enum(TipoVinculo::class)],
      'funcao' => ['required', Rule::enum(Funcao::class)],
    ]);

    $user = Auth::user();

    if ($user->status_cadastro !== 'ATIVO') {
      return back()->with('error', 'Seu cadastro não está ativo. Entre em contato com o administrador.');
    }

    $jaSolicitado = UsuarioProjeto::where('usuario_id', $user->id)
      ->where('projeto_id', $request->projeto_id)
      ->whereNull('data_fim')
      ->whereIn('status', ['PENDENTE', 'APROVADO'])
      ->exists();

    if ($jaSolicitado) {
      return back()->with('error', 'Você já possui solicitação ou vínculo ativo neste projeto.');
    }

    UsuarioProjeto::create([
      'usuario_id' => $user->id,
      'projeto_id' => $request->projeto_id,
      'tipo_vinculo' => $request->tipo_vinculo,
      'funcao' => $request->funcao,
      'status' => StatusVinculoProjeto::PENDENTE,
      'carga_horaria_semanal' => $request->carga_horaria_semanal,
      'data_inicio' => $request->data_inicio,
    ]);

    return back()->with('success', 'Solicitação de vínculo enviada com sucesso!');
  }
}
