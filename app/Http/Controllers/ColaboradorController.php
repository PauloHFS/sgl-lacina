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
        // Log::debug('Busca de colaboradores iniciada', [
        //     'search' => $request->input('search'),
        // ]);

        // $search = $request->input('search', '');

        // if (!empty($search)) {
        //     $colaboradores = Colaborador::search($search)
        //         ->query(function ($builder) {
        //             $builder->join('users', 'colaboradores.id', '=', 'users.id')
        //                 ->select('colaboradores.*', 'users.name', 'users.email');
        //         })
        //         ->paginate(10)
        //         ->appends($request->input('search'));
        // } else {
        //     $colaboradoresQuery = Colaborador::query()
        //         ->join('users', 'colaboradores.id', '=', 'users.id')
        //         ->select('colaboradores.*', 'users.name', 'users.email');

        //     $colaboradores = $colaboradoresQuery
        //         ->orderBy('colaboradores.created_at', 'desc')
        //         ->paginate(10)
        //         ->appends($request->input('search'));
        // }

        // Log::debug('Busca de colaboradores concluída', [
        //     'total' => $colaboradores->total(),
        // ]);

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

        // TODO: refatora isso para usar a classe de UsuarioProjeto
        $vinculos = DB::table('usuario_projeto')
            ->where('usuario_id', $usuario->id)
            ->orderByDesc('data_inicio')
            ->get();

        $status_cadastro = 'INATIVO';
        $projetos_atuais = [];
        $projeto_solicitado = null;
        $vinculo_id = null;

        Log::debug('Colaborador encontrado', [
            'usuario' => $usuario,
        ]);


        if ($usuario->status_cadastro === StatusCadastro::PENDENTE) {
            $status_cadastro = 'VINCULO_PENDENTE';
        } else if (
            $usuario->status_cadastro === StatusCadastro::ACEITO &&
            $vinculos->where('status', 'PENDENTE')->count() > 0 // TODO: verificar pq num funciona com o enum StatusVinculoProjeto
        ) {
            // Usuário aceito no laboratório, mas com vínculo pendente em projeto
            $status_cadastro = 'APROVACAO_PENDENTE';
            $vinculoPendenteProjeto = $vinculos->first(function ($v) {
                return $v->status === 'PENDENTE'; // TODO: verificar pq num funciona com o enum StatusVinculoProjeto
            });
            if ($vinculoPendenteProjeto) {
                $projeto_solicitado = Projeto::find($vinculoPendenteProjeto->projeto_id);
            }
        } else if (
            $usuario->status_cadastro === StatusCadastro::ACEITO &&
            // $vinculos->where('status', StatusVinculoProjeto::APROVADO) // TODO: verificar pq num funciona com o enum StatusVinculoProjeto
            $vinculos->where('status', 'APROVADO')
            // ->where('data_fim', '>', now()) TODO: Verificar essa validação
            ->count() > 0
        ) {
            // Usuário com vínculo aprovado e ativo em algum projeto
            $status_cadastro = 'ATIVO';
            $projetos_atuais = Projeto::whereIn(
                'id',
                $vinculos->where('status', 'APROVADO')
                    // ->where('data_fim', '>', now()) TODO: Verificar essa validação
                    ->pluck('projeto_id')
            )->get(['id', 'nome']);
        } else if (
            $usuario->status_cadastro === StatusCadastro::ACEITO &&
            $vinculos->where('status', StatusVinculoProjeto::INATIVO)
            ->where('data_fim', '<', now())
            ->count() > 0
        ) {
            // Usuário com vínculo inativo em todos os projetos
            $status_cadastro = 'INATIVO';
        }

        Log::debug('Status do colaborador', [
            'colaborador_id' => $usuario->id,
            'status_cadastro' => $status_cadastro,
        ]);

        return inertia('Colaboradores/Show', [
            'colaborador' => [
                'id' => $usuario->id,
                'name' => $usuario->name,
                'email' => $usuario->email,
                'linkedin_url' => $usuario->linkedin_url,
                'github_url' => $usuario->github_url,
                'figma_url' => $usuario->figma_url,
                'foto_url' => $usuario->foto_url,
                'area_atuacao' => $usuario->area_atuacao,
                'tecnologias' => $usuario->tecnologias,
                'curriculo' => $usuario->curriculo,
                'cpf' => $usuario->cpf,
                'conta_bancaria' => $usuario->conta_bancaria,
                'agencia' => $usuario->agencia,
                'codigo_banco' => $usuario->codigo_banco,
                'rg' => $usuario->rg,
                'uf_rg' => $usuario->uf_rg,
                'telefone' => $usuario->telefone,
                'created_at' => $usuario->created_at,
                'updated_at' => $usuario->updated_at,
                'status_cadastro' => $status_cadastro,
                'projeto_solicitado' => $projeto_solicitado
                    ? [
                        'id' => $projeto_solicitado->id,
                        'nome' => $projeto_solicitado->nome,
                    ]
                    : null,
                'projetos_atuais' => collect($projetos_atuais)->map(function ($p) {
                    return [
                        'id' => $p->id,
                        'nome' => $p->nome,
                    ];
                }),
            ],
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
