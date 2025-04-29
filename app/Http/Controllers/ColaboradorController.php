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
use App\Models\UsuarioVinculo;
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
            $usuarios = User::where('statusCadastro', 'PENDENTE')->paginate(10);
        } else if ($status == 'aprovacao_pendente') {
            // Usuários que estão na tabela de vinculo e com o status do vinculo pendente
            $usuarios = User::whereIn('id', function ($query) {
                $query->select('usuario_id')
                    ->from('usuario_vinculo')
                    ->where('tipo_vinculo', TipoVinculo::COLABORADOR)
                    ->where('status', StatusVinculoProjeto::PENDENTE);
            })->paginate(10);
        } else if ($status == 'ativos') {
            /*
            Usuários que estão na tabela de vinculo e todos os data_fim são maiores de now() e o status do vinculo é ativo
            */
            $usuarios = User::whereIn('id', function ($query) {
                $query->select('usuario_id')
                    ->from('usuario_vinculo')
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
                    ->from('usuario_vinculo')
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
        $colaborador = User::findOrFail($id);

        // Busca vínculos do colaborador
        $vinculos = DB::table('usuario_vinculo')
            ->where('usuario_id', $colaborador->id)
            ->orderByDesc('data_inicio')
            ->get();

        // Status e projetos
        $statusCadastro = 'INATIVO';
        $projetos_atuais = [];
        $projeto_solicitado = null;
        $vinculo_id = null;

        Log::debug('Colaborador encontrado', [
            'colaborador_id' => $colaborador->id,
            'statusCadastro' => $colaborador->statusCadastro,
            'vinculos' => $vinculos,
        ]);


        if ($colaborador->statusCadastro === StatusCadastro::PENDENTE) {
            $statusCadastro = 'VINCULO_PENDENTE';
        } else if (
            $colaborador->statusCadastro === StatusCadastro::ACEITO &&
            $vinculos->where('status', 'PENDENTE')->count() > 0 // TODO: verificar pq num funciona com o enum StatusVinculoProjeto
        ) {
            // Usuário aceito no laboratório, mas com vínculo pendente em projeto
            $statusCadastro = 'APROVACAO_PENDENTE';
            $vinculoPendenteProjeto = $vinculos->first(function ($v) {
                return $v->status === 'PENDENTE'; // TODO: verificar pq num funciona com o enum StatusVinculoProjeto
            });
            if ($vinculoPendenteProjeto) {
                $projeto_solicitado = Projeto::find($vinculoPendenteProjeto->projeto_id);
            }
        } else if (
            $colaborador->statusCadastro === StatusCadastro::ACEITO &&
            // $vinculos->where('status', StatusVinculoProjeto::APROVADO) // TODO: verificar pq num funciona com o enum StatusVinculoProjeto
            $vinculos->where('status', 'APROVADO')
            // ->where('data_fim', '>', now()) TODO: Verificar essa validação
            ->count() > 0
        ) {
            // Usuário com vínculo aprovado e ativo em algum projeto
            $statusCadastro = 'ATIVO';
            $projetos_atuais = Projeto::whereIn(
                'id',
                $vinculos->where('status', 'APROVADO')
                    // ->where('data_fim', '>', now()) TODO: Verificar essa validação
                    ->pluck('projeto_id')
            )->get(['id', 'nome']);
        } else if (
            $colaborador->statusCadastro === StatusCadastro::ACEITO &&
            $vinculos->where('status', StatusVinculoProjeto::INATIVO)
            ->where('data_fim', '<', now())
            ->count() > 0
        ) {
            // Usuário com vínculo inativo em todos os projetos
            $statusCadastro = 'INATIVO';
        }

        Log::debug('Status do colaborador', [
            'colaborador_id' => $colaborador->id,
            'statusCadastro' => $statusCadastro,
        ]);

        return inertia('Colaboradores/Show', [
            'colaborador' => [
                'id' => $colaborador->id,
                'name' => $colaborador->name,
                'email' => $colaborador->email,
                'linkedin_url' => $colaborador->linkedin_url,
                'github_url' => $colaborador->github_url,
                'figma_url' => $colaborador->figma_url,
                'foto_url' => $colaborador->foto_url,
                'area_atuacao' => $colaborador->area_atuacao,
                'tecnologias' => $colaborador->tecnologias,
                'curriculo' => $colaborador->curriculo,
                'cpf' => $colaborador->cpf,
                'conta_bancaria' => $colaborador->conta_bancaria,
                'agencia' => $colaborador->agencia,
                'codigo_banco' => $colaborador->codigo_banco,
                'rg' => $colaborador->rg,
                'uf_rg' => $colaborador->uf_rg,
                'telefone' => $colaborador->telefone,
                'created_at' => $colaborador->created_at,
                'updated_at' => $colaborador->updated_at,
                'statusCadastro' => $statusCadastro,
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
        $colaborador->statusCadastro = 'ACEITO';
        $colaborador->save();

        // Aqui você pode disparar eventos/emails se necessário

        return redirect()->back()->with('success', 'Colaborador aceito com sucesso.');
    }

    public function recusar(User $colaborador, Request $request)
    {
        $colaborador->statusCadastro = 'RECUSADO';
        $colaborador->save();

        // Aqui você pode disparar eventos/emails se necessário

        return redirect()->back()->with('success', 'Colaborador recusado com sucesso.');
    }

    public function aceitarVinculo(User $colaborador)
    {

        $vinculo = UsuarioVinculo::where('usuario_id', $colaborador->id)
            ->where('tipo_vinculo', TipoVinculo::COLABORADOR)
            ->where('status', StatusVinculoProjeto::PENDENTE)
            ->first();

        if (!$vinculo) {
            return redirect()->back()->with('error', 'Vínculo não encontrado.');
        }

        UsuarioVinculo::where('projeto_id', $vinculo->projeto_id)
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
        $vinculo = UsuarioVinculo::where('usuario_id', $colaborador->id)
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
