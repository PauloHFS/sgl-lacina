<?php

namespace App\Http\Controllers;

use App\Enums\StatusCadastro;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Projeto;
use Inertia\Inertia;
use App\Enums\TipoVinculo;
use App\Events\PreColaboradorAceito;
use App\Enums\StatusVinculoProjeto;
use App\Models\UsuarioProjeto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ColaboradorController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status');

        $usuarios = null;

        if ($status == 'vinculo_pendente') {
            // Usuários que estão com o status cadastro pendente
            $usuarios = User::where('status_cadastro', 'PENDENTE')->paginate(10);
        } else if ($status == 'aprovacao_pendente') {
            // Usuários que estão na tabela de vinculo e com o status do vinculo pendente
            $usuarios = User::whereIn('id', function ($query) {
                $query->select('usuario_id')
                    ->from('usuario_projeto')
                    ->where('tipo_vinculo', TipoVinculo::COLABORADOR)
                    ->where('status', StatusVinculoProjeto::PENDENTE);
            })->paginate(10);
        } else if ($status == 'ativos') {
            /*
            Usuários que estão na tabela de vinculo e todos os data_fim são maiores de now() e o status do vinculo é ativo
            */
            $usuarios = User::whereIn('id', function ($query) {
                $query->select('usuario_id')
                    ->from('usuario_projeto')
                    ->where('tipo_vinculo', TipoVinculo::COLABORADOR)
                    ->where('status', StatusVinculoProjeto::APROVADO)
                    ->where('data_fim', '>', now());
            })->paginate(10);
        } else if ($status == 'inativos') {
            /*
            Usuários que estão na tabela de vinculo e mas todos os data_fim são menores de now()
            */
            $usuarios = User::whereIn('id', function ($query) {
                $query->select('usuario_id')
                    ->from('usuario_projeto')
                    ->where('tipo_vinculo', TipoVinculo::COLABORADOR)
                    ->where('status', StatusVinculoProjeto::INATIVO)
                    ->where('data_fim', '<', now());
            })->paginate(10);
        }

        // Log::debug('Busca de colaboradores concluída', [
        //     'total' => $usuarios->total(),
        // ]);

        if ($usuarios) {
            return Inertia::render('Colaboradores/Index', [
                'colaboradores' => $usuarios,
            ]);
        }
        return Inertia::render('Colaboradores/Index', []);
    }

    public function show($id)
    {
        $usuario = User::findOrFail($id);

        // Eager load related projects for vinculations to optimize queries
        $vinculos = UsuarioProjeto::with('projeto')
            ->where('usuario_id', $usuario->id)
            ->orderByDesc('data_inicio')
            ->get();

        $statusCadastroView = 'INATIVO'; // Default status for the view
        $projetosAtuais = collect();
        $vinculoPendente = null;

        // Ensure status_cadastro is an enum instance for reliable comparison
        $userSystemStatus = $usuario->status_cadastro instanceof StatusCadastro
            ? $usuario->status_cadastro
            : StatusCadastro::tryFrom($usuario->status_cadastro);

        if ($userSystemStatus === StatusCadastro::PENDENTE) {
            $statusCadastroView = 'VINCULO_PENDENTE';
        } elseif ($userSystemStatus === StatusCadastro::ACEITO) {
            // Check for any project vinculation request that is pending approval
            $vinculoPendente = $vinculos->first(function ($vinculo) {
                // Ensure vinculo->status is an enum instance or correctly compared to its value
                $vinculoStatus = $vinculo->status instanceof StatusVinculoProjeto
                    ? $vinculo->status
                    : StatusVinculoProjeto::tryFrom($vinculo->status);
                return $vinculoStatus === StatusVinculoProjeto::PENDENTE;
            });

            if ($vinculoPendente) {
                $statusCadastroView = 'APROVACAO_PENDENTE';
                // $projetoSolicitado = $vinculoPendente->projeto; // Use eager-loaded project
            } else {
                // Check for active project vinculations
                // The original code had a TODO for data_fim validation.
                // Add '&& (!$v->data_fim || $v->data_fim->isFuture())' if needed.
                $vinculosAprovados = $vinculos->filter(function ($vinculo) {
                    $vinculoStatus = $vinculo->status instanceof StatusVinculoProjeto
                        ? $vinculo->status
                        : StatusVinculoProjeto::tryFrom($vinculo->status);
                    return $vinculoStatus === StatusVinculoProjeto::APROVADO;
                });

                if ($vinculosAprovados->isNotEmpty()) {
                    $statusCadastroView = 'ATIVO';
                    $projetosAtuais = $vinculosAprovados->map(fn($v) => $v->projeto)
                        ->filter() // Remove any null projects if a vinculo has an invalid projeto_id
                        ->unique('id') // Ensure unique projects
                        ->values();
                }
                // If user is ACEITO but has no PENDING or ACTIVE vinculations,
                // $statusCadastroView remains 'INATIVO'.
            }
        }
        // If $userSystemStatus is RECUSADO, $statusCadastroView will also remain 'INATIVO'.

        Log::debug('Colaborador encontrado', ['usuario_id' => $usuario->id, 'raw_status' => $usuario->status_cadastro]);
        Log::debug('Status do colaborador para a view', [
            'colaborador_id' => $usuario->id,
            'status_calculado' => $statusCadastroView,
        ]);

        // Prepare data for Inertia response
        $colaboradorData = $usuario->only([
            'id',
            'name',
            'email',
            'linkedin_url',
            'github_url',
            'figma_url',
            'foto_url',
            'area_atuacao',
            'tecnologias',
            'curriculo',
            'cpf',
            'conta_bancaria',
            'agencia',
            'codigo_banco', // Assuming 'codigo_banco' is a field on User model or accessor
            'rg',
            'uf_rg',
            'telefone',
        ]);

        $colaboradorData['created_at'] = $usuario->created_at?->toIso8601String();
        $colaboradorData['updated_at'] = $usuario->updated_at?->toIso8601String();
        $colaboradorData['status_cadastro'] = $statusCadastroView; // Calculated status for UI logic
        $colaboradorData['vinculo'] = $vinculoPendente
            ? $vinculoPendente
            : null;
        $colaboradorData['projetos_atuais'] = $projetosAtuais->map(fn($p) => ['id' => $p->id, 'nome' => $p->nome]);

        Log::debug('Dados do colaborador preparados para a view', [
            'colaborador_id' => $usuario->id,
            'dados' => $colaboradorData,
        ]);

        return inertia('Colaboradores/Show', [
            'colaborador' => $colaboradorData,
        ]);
    }

    public function aceitar(User $colaborador, Request $request)
    {
        $colaborador->status_cadastro = 'ACEITO';
        $colaborador->save();

        // Aqui você pode disparar eventos/emails se necessário

        return redirect()->back()->with('success', 'Colaborador aceito com sucesso.');
    }

    public function recusar(User $colaborador, Request $request)
    {
        $colaborador->status_cadastro = 'RECUSADO';
        $colaborador->save();

        // Aqui você pode disparar eventos/emails se necessário

        return redirect()->back()->with('success', 'Colaborador recusado com sucesso.');
    }

    public function aceitarVinculo(User $colaborador)
    {

        $vinculo = UsuarioProjeto::where('usuario_id', $colaborador->id)
            ->where('tipo_vinculo', TipoVinculo::COLABORADOR)
            ->where('status', StatusVinculoProjeto::PENDENTE)
            ->first();

        if (!$vinculo) {
            return redirect()->back()->with('error', 'Vínculo não encontrado.');
        }

        UsuarioProjeto::where('projeto_id', $vinculo->projeto_id)
            ->where('usuario_id', $vinculo->usuario_id)
            // ->whereNull('data_fim')
            ->update([
                'status' => 'APROVADO',
                'data_inicio' => now(),
                'data_fim' => now()->addMonths(6),
            ]);

        // Aqui você pode disparar eventos/emails se necessário

        return redirect()->back()->with('success', 'Colaborador aceito com sucesso.');
    }

    public function recusarVinculo(User $colaborador, Request $request)
    {
        $vinculo = UsuarioProjeto::where('usuario_id', $colaborador->id)
            ->where('tipo_vinculo', TipoVinculo::COLABORADOR)
            ->where('status', StatusVinculoProjeto::PENDENTE)
            ->first();
        if (!$vinculo) {
            return redirect()->back()->with('error', 'Vínculo não encontrado.');
        }
        $vinculo->status = 'INATIVO'; // StatusVinculoProjeto::INATIVO //TODO: criar um enum para recusado no vinculo
        $vinculo->data_fim = now();
        $vinculo->save();

        // Aqui você pode disparar eventos/emails se necessário

        return redirect()->back()->with('success', 'Colaborador recusado com sucesso.');
    }
}
