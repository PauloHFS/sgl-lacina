<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Colaborador;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use App\Enums\TipoVinculo;
use App\Events\PreColaboradorAceito;
use App\Models\UsuarioVinculo;

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

        if ($status == 'ativos') {
            /*
            Usuários que estão na tabela de vinculo e todos os data_fim são maiores de now()
            */
            $usuarios = User::whereIn('id', function ($query) {
                $query->select('usuario_id')
                    ->from('usuario_vinculo')
                    ->where('tipo_vinculo', TipoVinculo::COLABORADOR)
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
                    ->where('data_fim', '<', now());
            })->paginate(10);
        } else if ($status == 'pendentes') {
            /*
            Usuários que estão com o status cadastro pendente
            */

            $usuarios = User::where('statusCadastro', 'PENDENTE')->paginate(10);
        }

        Log::debug('Busca de colaboradores concluída', [
            'total' => $usuarios->total(),
        ]);

        return Inertia::render('Colaboradores/Index', [
            'colaboradores' => $usuarios,
        ]);
    }

    /**
     * Rederiza a view de Validar o pre cadastro do colaborador.
     * recebe o id na url
     */
    public function showValidateUsuario(Request $request, string $preCandidatoUserId)
    {
        $usuario = User::where('id', $preCandidatoUserId)->first();

        if ($usuario) {
            return Inertia::render('PreCandidato/AvaliacaoInicial', [
                'status' => 'success',
                'message' => 'Colaborador encontrado.',
                'user' => $usuario,
            ]);
        }

        return Inertia::render('PreCandidato/AvaliacaoInicial', [
            'user' => null,
            'status' => 'error',
            'message' => 'Colaborador não encontrado.',
        ]);
    }

    public function aceitar(Request $request)
    {
        $preCandidatoUserId = $request->input('preCandidatoUserId');

        if (!$preCandidatoUserId) {
            return Inertia::render('PreCandidato/AvaliacaoInicial', [
                'user' => null,
                'status' => 'error',
                'message' => 'ID do colaborador não fornecido.',
            ]);
        }
        $usuario = User::where('id', $preCandidatoUserId)->first();

        if ($usuario) {
            // $colaborador = new Colaborador();
            // $colaborador->id = $usuario->id;
            // $colaborador->save();

            // // criar colaborador vinculo
            // $colaborador->vinculo()->create([
            //     'tipo' => TipoVinculo::ALUNO_GRADUACAO,
            //     'data_inicio' => now(),
            // ]);

            // dispara o evento de colaborador aceito
            event(new PreColaboradorAceito($usuario->id, $usuario->email));

            return Inertia::render('PreCandidato/AvaliacaoInicial', [
                'status' => 'success',
                'message' => 'Colaborador aceito com sucesso.',
                'user' => $usuario,
            ]);
        }

        return Inertia::render('PreCandidato/AvaliacaoInicial', [
            'user' => null,
            'status' => 'error',
            'message' => 'Colaborador não encontrado.',
        ]);
    }
}
