<?php

namespace App\Http\Controllers;

use App\Http\Requests\AusenciasRequest;
use App\Models\Ausencia;
use App\Models\Projeto;
use App\Models\UsuarioProjeto;
use App\Services\HorariosCacheService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use App\Enums\StatusAusencia;
use Illuminate\Validation\Rule;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AusenciasController extends Controller
{
    use AuthorizesRequests;

    protected HorariosCacheService $horariosCacheService;

    public function __construct(HorariosCacheService $horariosCacheService)
    {
        $this->horariosCacheService = $horariosCacheService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {

        $user = Auth::user();

        if (Auth::user()->isCoordenador()) {
            return Inertia::render('Ausencias/IndexCoordenador', [
                'ausencias' => Ausencia::with(['projeto', 'usuario'])
                    ->orderBy('data_inicio', 'desc', 'status')
                    ->paginate(15),
            ]);
        }

        // Obter projetos ativos do usuário
        $projetosAtivos = UsuarioProjeto::with('projeto')
            ->where('usuario_id', $user->id)
            ->where('status', 'APROVADO')
            ->whereNull('data_fim')
            ->get()
            ->pluck('projeto');

        // Filtros
        $filtros = [
            'data_inicio' => $request->get('data_inicio'),
            'data_fim' => $request->get('data_fim'),
            'projeto_id' => $request->get('projeto_id'),
        ];

        // Query base
        $query = Ausencia::with(['projeto'])
            ->where('usuario_id', $user->id)
            ->orderBy('data_inicio', 'desc');

        // Aplicar filtros
        if ($filtros['data_inicio']) {
            $query->where('data', '>=', $filtros['data_inicio']);
        }

        if ($filtros['data_fim']) {
            $query->where('data', '<=', $filtros['data_fim']);
        }

        if ($filtros['projeto_id']) {
            $query->where('projeto_id', $filtros['projeto_id']);
        }

        $ausencias = $query->paginate(15);

        return Inertia::render('Ausencias/Index', [
            'ausencias' => $ausencias,
            'projetosAtivos' => $projetosAtivos,
            'filtros' => $filtros,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $user = Auth::user();

        $projetosAtivos = UsuarioProjeto::with('projeto')
            ->where('usuario_id', $user->id)
            ->where('status', 'APROVADO')
            ->whereNull('data_fim')
            ->get()
            ->map(function ($vinculo) {
                return [
                    'id' => $vinculo->projeto->id,
                    'nome' => $vinculo->projeto->nome,
                    'cliente' => $vinculo->projeto->cliente,
                ];
            });

        $horasPorProjetoPorDia = $this->horariosCacheService->getHorasPorDiaDaSemanaProjetoPorProjeto($user);

        return Inertia::render('Ausencias/Create', [
            'projetosAtivos' => $projetosAtivos,
            'horasPorProjetoPorDia' => $horasPorProjetoPorDia,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AusenciasRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $validated = $request->validated();

        // Verificar se o usuário está vinculado ao projeto
        $vinculo = UsuarioProjeto::where('usuario_id', $user->id)
            ->where('projeto_id', $validated['projeto_id'])
            ->where('status', 'APROVADO')
            ->whereNull('data_fim')
            ->first();

        if (!$vinculo) {
            return redirect()->back()
                ->withErrors(['projeto_id' => 'Você não está vinculado a este projeto ou o vínculo não está ativo.'])
                ->withInput();
        }

        try {
            DB::transaction(function () use ($validated) {
                // Se houver horários de compensação, extrair as datas de início e fim
                if (!empty($validated['compensacao_horarios'])) {
                    $horarios = $validated['compensacao_horarios'];
                    if (is_array($horarios) && count($horarios) > 0) {
                        $datas = array_column($horarios, 'data');
                        sort($datas);
                        $validated['compensacao_data_inicio'] = $datas[0];
                        $validated['compensacao_data_fim'] = end($datas);
                    }
                }

                Ausencia::create($validated);
            });

            return redirect()->route('ausencia.index')
                ->with('success', 'Ausencia criada com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao criar Ausencia', [
                'exception' => $e,
                'user_id' => $user->id ?? null,
                'data' => $validated ?? [],
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Erro ao criar Ausencia. Tente novamente.'])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Ausencia $ausencia): Response
    {
        $this->authorize('view', $ausencia);

        $ausencia->load(['projeto', 'usuario']);

        return Inertia::render('Ausencias/Show', [
            'ausencia' => $ausencia,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ausencia $ausencia): Response
    {
        $this->authorize('update', $ausencia);

        $user = Auth::user();

        // Obter projetos ativos do usuário
        $projetosAtivos = UsuarioProjeto::with('projeto')
            ->where('usuario_id', $user->id)
            ->where('status', 'APROVADO')
            ->whereNull('data_fim')
            ->get()
            ->map(function ($vinculo) {
                return [
                    'id' => $vinculo->projeto->id,
                    'nome' => $vinculo->projeto->nome,
                    'cliente' => $vinculo->projeto->cliente,
                ];
            });

        // Obter horas trabalhadas por projeto e dia da semana do cache
        $horasPorProjetoPorDia = $this->horariosCacheService->getHorasPorDiaDaSemanaProjetoPorProjeto($user);

        return Inertia::render('Ausencias/Edit', [
            'ausencia' => $ausencia,
            'projetosAtivos' => $projetosAtivos,
            'horasPorProjetoPorDia' => $horasPorProjetoPorDia,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AusenciasRequest $request, Ausencia $ausencia): RedirectResponse
    {
        if ($ausencia->usuario_id !== Auth::id()) {
            abort(403, 'Você não tem permissão para editar esta ausencia.');
        }

        $validated = $request->validated();

        // Verificar se o usuário está vinculado ao projeto
        $vinculo = UsuarioProjeto::where('usuario_id', Auth::id())
            ->where('projeto_id', $validated['projeto_id'])
            ->where('status', 'APROVADO')
            ->whereNull('data_fim')
            ->first();

        if (!$vinculo) {
            return redirect()->back()
                ->withErrors(['projeto_id' => 'Você não está vinculado a este projeto ou o vínculo não está ativo.'])
                ->withInput();
        }

        try {
            DB::transaction(function () use ($validated, $ausencia) {
                // Se houver horários de compensação, extrair as datas de início e fim
                if (!empty($validated['compensacao_horarios'])) {
                    $horarios = $validated['compensacao_horarios'];
                    if (is_array($horarios) && count($horarios) > 0) {
                        $datas = array_column($horarios, 'data');
                        sort($datas);
                        $validated['compensacao_data_inicio'] = $datas[0];
                        $validated['compensacao_data_fim'] = end($datas);
                    } else {
                        // Se o array de horários estiver vazio, zera as datas
                        $validated['compensacao_data_inicio'] = null;
                        $validated['compensacao_data_fim'] = null;
                    }
                } else {
                    $validated['compensacao_data_inicio'] = null;
                    $validated['compensacao_data_fim'] = null;
                }

                $ausencia->update($validated);
            });

            return redirect()->route('ausencias.index')
                ->with('success', 'Ausencia atualizada com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar ausencia', [
                'exception' => $e,
                'user_id' => Auth::id(),
                'ausencia_id' => $ausencia->id,
                'data' => $validated ?? [],
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Erro ao atualizar a ausencia. Tente novamente.'])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ausencia $ausencia): RedirectResponse
    {
        $this->authorize('delete', $ausencia);

        $hoje = now()->startOfDay();
        $dataReport = $ausencia->data->startOfDay();
        if ($dataReport->diffInDays($hoje, false) < 0 || $dataReport->diffInDays($hoje, false) > 1) {
            abort(403, 'Só é possível remover o daily report no mesmo dia ou no dia seguinte.');
        }

        try {
            $ausencia->forceDelete();

            return redirect()->route('daily-reports.index')
                ->with('success', 'Daily report removido com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Erro ao remover daily report. Tente novamente.']);
        }
    }

    public function updateStatus(Request $request, Ausencia $ausencia): RedirectResponse
    {
        $this->authorize('updateStatus', $ausencia);

        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in([StatusAusencia::APROVADO->value, StatusAusencia::REJEITADO->value])
            ]
        ]);

        if ($ausencia->status !== StatusAusencia::PENDENTE->value) {
            return redirect()->back()->withErrors(['error' => 'O status desta ausencia não pode mais ser alterado.']);
        }

        $ausencia->status = $validated['status'];
        $ausencia->save();

        $message = $validated['status'] === StatusAusencia::APROVADO->value
            ? 'Ausencia aprovada com sucesso!'
            : 'Ausencia recusada com sucesso.';

        return redirect()->back()->with('success', $message);
    }
}
