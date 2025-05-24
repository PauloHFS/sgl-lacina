<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UsuarioProjeto;
use App\Enums\StatusVinculoProjeto;
use App\Enums\TipoVinculo;
use App\Enums\Funcao;
use App\Enums\StatusCadastro;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProjetoVinculoController extends Controller
{
  public function create(Request $request)
  {
    $request->validate([
      'projeto_id' => 'required|exists:projetos,id',
      'data_inicio' => 'required|date',
      'carga_horaria_semanal' => 'required|integer|min:1|max:40',
      'tipo_vinculo' => ['required', Rule::enum(TipoVinculo::class)],
      'funcao' => ['required', Rule::enum(Funcao::class)],
      'trocar' => 'sometimes|boolean',
      'usuario_projeto_trocado_id' => 'sometimes|exists:usuario_projeto,id',
    ]);

    $user = Auth::user();


    if ($user->status_cadastro !== StatusCadastro::ACEITO) {
      return back()->with('error', 'Seu cadastro não está aceito. Entre em contato com o administrador.');
    }

    $jaSolicitado = UsuarioProjeto::where('usuario_id', $user->id)
      ->where('projeto_id', $request->projeto_id)
      ->whereNull('data_fim')
      ->whereIn('status', ['PENDENTE', 'APROVADO'])
      ->exists();

    if ($jaSolicitado) {
      return back()->with('error', 'Você já possui solicitação ou vínculo ativo neste projeto.');
    }

    if ($request->trocar) {

      // checar se ja nao tem um trocar true para esse usuario
      $jaTemTrocar = UsuarioProjeto::where('usuario_id', $user->id)->where('trocar', true)->exists();

      if ($jaTemTrocar) {
        return back()->with('error', 'Você já possui uma troca em andamento.');
      }

      DB::transaction(function () use ($request, $user) {
        UsuarioProjeto::whereId($request->usuario_projeto_trocado_id)
          ->update([
            'trocar' => true,
          ]);

        UsuarioProjeto::create([
          'usuario_id' => $user->id,
          'projeto_id' => $request->projeto_id,
          'tipo_vinculo' => $request->tipo_vinculo,
          'funcao' => $request->funcao,
          'status' => StatusVinculoProjeto::PENDENTE,
          'carga_horaria_semanal' => $request->carga_horaria_semanal,
          'data_inicio' => $request->data_inicio,
        ]);
      });
    } else {
      UsuarioProjeto::create([
        'usuario_id' => $user->id,
        'projeto_id' => $request->projeto_id,
        'tipo_vinculo' => $request->tipo_vinculo,
        'funcao' => $request->funcao,
        'status' => StatusVinculoProjeto::PENDENTE,
        'carga_horaria_semanal' => $request->carga_horaria_semanal,
        'data_inicio' => $request->data_inicio,
      ]);
    }

    return back()->with('success', 'Solicitação de vínculo enviada com sucesso!');
  }

  public function update(Request $request, $id)
  {
    $validatedData = $request->validate([
      'status' => ['sometimes', 'required', Rule::enum(StatusVinculoProjeto::class)],
      'carga_horaria_semanal' => 'sometimes|nullable|integer|min:1|max:40',
      'funcao' => ['sometimes', 'nullable', Rule::enum(Funcao::class)],
      'tipo_vinculo' => ['sometimes', 'nullable', Rule::enum(TipoVinculo::class)],
      'data_inicio' => 'sometimes|nullable|date',
      'data_fim' => 'sometimes|nullable|date|after_or_equal:data_inicio',
    ]);

    $usuarioProjeto = UsuarioProjeto::findOrFail($id);

    if ($request->filled('status')) {
      $usuarioProjeto->status = $validatedData['status'];

      if ($usuarioProjeto->status === StatusVinculoProjeto::APROVADO) {
        $vinculoAntigo = UsuarioProjeto::where('trocar', true)->where('usuario_id', $usuarioProjeto->usuario_id)->first();
        Log::info('Vínculo antigo encontrado: ', ['vinculoAntigo' => $vinculoAntigo, 'projetoAntigoId' => $usuarioProjeto->projeto_antigo_id]);
        if ($vinculoAntigo) {
          $vinculoAntigo->trocar = false;
          $vinculoAntigo->status = StatusVinculoProjeto::ENCERRADO;
          $vinculoAntigo->data_fim = $usuarioProjeto->data_inicio;
          $vinculoAntigo->save();
        }
      }

      if ($validatedData['status'] === StatusVinculoProjeto::ENCERRADO && !$request->filled('data_fim')) {
        $usuarioProjeto->data_fim = now();
      } elseif ($validatedData['status'] !== StatusVinculoProjeto::ENCERRADO) {
        // Se o status não for ENCERRADO, garantir que data_fim seja null,
        // a menos que explicitamente fornecido e diferente de ENCERRADO (cenário incomum).
        if (!$request->filled('data_fim')) {
          $usuarioProjeto->data_fim = null;
        }
      }
    };

    if ($request->filled('carga_horaria_semanal')) {
      $usuarioProjeto->carga_horaria_semanal = $validatedData['carga_horaria_semanal'];
    }

    if ($request->filled('funcao')) {
      $usuarioProjeto->funcao = $validatedData['funcao'];
    }

    if ($request->filled('tipo_vinculo')) {
      $usuarioProjeto->tipo_vinculo = $validatedData['tipo_vinculo'];
    }

    if ($request->filled('data_inicio')) {
      $usuarioProjeto->data_inicio = $validatedData['data_inicio'];
    }

    if ($request->filled('data_fim')) {
      $usuarioProjeto->data_fim = $validatedData['data_fim'];
    }

    $usuarioProjeto->save();

    return back()->with('success', 'Vínculo com projeto atualizado com sucesso!');
  }
}
