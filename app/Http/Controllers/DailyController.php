<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDailyRequest;
use App\Models\Daily;
use App\Models\UsuarioProjeto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DailyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Daily::where('usuario_id', $user->id)
            ->with(['usuarioProjeto.projeto'])
            ->orderBy('data', 'desc');

        // Filtrar por projeto se especificado
        if ($request->filled('projeto_id')) {
            $query->whereHas('usuarioProjeto', function ($q) use ($request) {
                $q->where('projeto_id', $request->projeto_id);
            });
        }

        // Filtrar por período se especificado
        if ($request->filled('data_inicio')) {
            $query->where('data', '>=', $request->data_inicio);
        }

        if ($request->filled('data_fim')) {
            $query->where('data', '<=', $request->data_fim);
        }

        $dailies = $query->paginate(15);

        return Inertia::render('Daily/Index', [
            'dailies' => $dailies,
            'filters' => $request->only(['projeto_id', 'data_inicio', 'data_fim']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();

        // Buscar projetos ativos do usuário
        $usuarioProjetos = UsuarioProjeto::where('usuario_id', $user->id)
            ->where('status', 'ATIVO')
            ->with(['projeto' => function ($query) {
                $query->select('id', 'nome');
            }])
            ->get()
            ->map(function ($usuarioProjeto) {
                return [
                    'id' => $usuarioProjeto->id,
                    'projeto_nome' => $usuarioProjeto->projeto->nome,
                    'funcao' => $usuarioProjeto->funcao,
                ];
            });

        return Inertia::render('Daily/Create', [
            'usuarioProjetos' => $usuarioProjetos,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDailyRequest $request)
    {
        $user = Auth::user();

        try {
            DB::beginTransaction();

            // Verificar se o usuário tem acesso ao projeto
            $usuarioProjeto = UsuarioProjeto::where('id', $request->validated()['usuario_projeto_id'])
                ->where('usuario_id', $user->id)
                ->where('status', 'ATIVO')
                ->first();

            if (!$usuarioProjeto) {
                return back()->withErrors([
                    'usuario_projeto_id' => 'Você não tem acesso a este projeto ou ele não está ativo.'
                ]);
            }

            // Verificar se já existe um daily para este usuário/projeto/data
            $existeDaily = Daily::where('usuario_id', $user->id)
                ->where('usuario_projeto_id', $request->validated()['usuario_projeto_id'])
                ->where('data', $request->validated()['data'])
                ->exists();

            if ($existeDaily) {
                return back()->withErrors([
                    'data' => 'Já existe um daily registrado para este projeto nesta data.'
                ]);
            }

            $daily = Daily::create([
                'usuario_id' => $user->id,
                'usuario_projeto_id' => $request->validated()['usuario_projeto_id'],
                'data' => $request->validated()['data'],
                'ontem' => $request->validated()['ontem'],
                'observacoes' => $request->validated()['observacoes'] ?? null,
                'hoje' => $request->validated()['hoje'],
                'carga_horaria' => $request->validated()['carga_horaria'],
            ]);

            DB::commit();

            return redirect()->route('daily.index')->with('success', 'Daily registrado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Erro ao registrar o daily. Tente novamente.'
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Daily $daily)
    {
        $user = Auth::user();

        // Verificar se o daily pertence ao usuário autenticado
        if ($daily->usuario_id !== $user->id) {
            abort(403, 'Você não tem permissão para visualizar este daily.');
        }

        $daily->load(['usuarioProjeto.projeto']);

        return Inertia::render('Daily/Show', [
            'daily' => $daily,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Daily $daily)
    {
        $user = Auth::user();

        // Verificar se o daily pertence ao usuário autenticado
        if ($daily->usuario_id !== $user->id) {
            abort(403, 'Você não tem permissão para editar este daily.');
        }

        // Verificar se o daily pode ser editado (ex: apenas no mesmo dia)
        if ($daily->data->format('Y-m-d') !== now()->format('Y-m-d')) {
            return redirect()->route('daily.index')->withErrors([
                'error' => 'Só é possível editar dailies do dia atual.'
            ]);
        }

        // Buscar projetos ativos do usuário
        $usuarioProjetos = UsuarioProjeto::where('usuario_id', $user->id)
            ->where('status', 'ATIVO')
            ->with(['projeto' => function ($query) {
                $query->select('id', 'nome');
            }])
            ->get()
            ->map(function ($usuarioProjeto) {
                return [
                    'id' => $usuarioProjeto->id,
                    'projeto_nome' => $usuarioProjeto->projeto->nome,
                    'funcao' => $usuarioProjeto->funcao,
                ];
            });

        return Inertia::render('Daily/Edit', [
            'daily' => $daily,
            'usuarioProjetos' => $usuarioProjetos,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreDailyRequest $request, Daily $daily)
    {
        $user = Auth::user();

        // Verificar se o daily pertence ao usuário autenticado
        if ($daily->usuario_id !== $user->id) {
            abort(403, 'Você não tem permissão para editar este daily.');
        }

        // Verificar se o daily pode ser editado (ex: apenas no mesmo dia)
        if ($daily->data->format('Y-m-d') !== now()->format('Y-m-d')) {
            return redirect()->route('daily.index')->withErrors([
                'error' => 'Só é possível editar dailies do dia atual.'
            ]);
        }

        try {
            DB::beginTransaction();

            // Verificar se o usuário tem acesso ao projeto
            $usuarioProjeto = UsuarioProjeto::where('id', $request->validated()['usuario_projeto_id'])
                ->where('usuario_id', $user->id)
                ->where('status', 'ATIVO')
                ->first();

            if (!$usuarioProjeto) {
                return back()->withErrors([
                    'usuario_projeto_id' => 'Você não tem acesso a este projeto ou ele não está ativo.'
                ]);
            }

            // Se mudou o projeto ou a data, verificar se já existe outro daily
            if (
                $daily->usuario_projeto_id !== $request->validated()['usuario_projeto_id'] ||
                $daily->data->format('Y-m-d') !== $request->validated()['data']
            ) {
                $existeDaily = Daily::where('usuario_id', $user->id)
                    ->where('usuario_projeto_id', $request->validated()['usuario_projeto_id'])
                    ->where('data', $request->validated()['data'])
                    ->where('id', '!=', $daily->id)
                    ->exists();

                if ($existeDaily) {
                    return back()->withErrors([
                        'data' => 'Já existe um daily registrado para este projeto nesta data.'
                    ]);
                }
            }

            $daily->update([
                'usuario_projeto_id' => $request->validated()['usuario_projeto_id'],
                'data' => $request->validated()['data'],
                'ontem' => $request->validated()['ontem'],
                'observacoes' => $request->validated()['observacoes'] ?? null,
                'hoje' => $request->validated()['hoje'],
                'carga_horaria' => $request->validated()['carga_horaria'],
            ]);

            DB::commit();

            return redirect()->route('daily.index')->with('success', 'Daily atualizado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Erro ao atualizar o daily. Tente novamente.'
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Daily $daily)
    {
        $user = Auth::user();

        // Verificar se o daily pertence ao usuário autenticado
        if ($daily->usuario_id !== $user->id) {
            abort(403, 'Você não tem permissão para excluir este daily.');
        }

        // Verificar se o daily pode ser excluído (ex: apenas no mesmo dia)
        if ($daily->data->format('Y-m-d') !== now()->format('Y-m-d')) {
            return redirect()->route('daily.index')->withErrors([
                'error' => 'Só é possível excluir dailies do dia atual.'
            ]);
        }

        try {
            $daily->delete();

            return redirect()->route('daily.index')->with('success', 'Daily excluído com sucesso!');
        } catch (\Exception $e) {
            return redirect()->route('daily.index')->withErrors([
                'error' => 'Erro ao excluir o daily. Tente novamente.'
            ]);
        }
    }
}
