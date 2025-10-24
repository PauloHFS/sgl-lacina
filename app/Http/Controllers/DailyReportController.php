<?php

namespace App\Http\Controllers;

use App\Http\Requests\DailyReportRequest;
use App\Models\DailyReport;
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

class DailyReportController extends Controller
{
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
        $query = DailyReport::with(['projeto'])
            ->where('usuario_id', $user->id)
            ->orderBy('data', 'desc');

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

        $dailyReports = $query->paginate(15);

        return Inertia::render('DailyReports/Index', [
            'dailyReports' => $dailyReports,
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

        return Inertia::render('DailyReports/Create', [
            'projetosAtivos' => $projetosAtivos,
            'horasPorProjetoPorDia' => $horasPorProjetoPorDia,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DailyReportRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $validated = $request->validated();

        // Verificar se já existe um daily report para esta data
        $existente = DailyReport::where('usuario_id', $user->id)
            ->where('data', $validated['data'])
            ->where('projeto_id', $validated['projeto_id'])
            ->first();

        if ($existente) {
            return redirect()->back()
                ->withErrors(['data' => 'Você já possui um daily report para esta data.'])
                ->withInput();
        }

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
            DB::transaction(function () use ($validated, $user) {
                $dailyReport = new DailyReport($validated);
                $dailyReport->usuario_id = $user->id;

                // Se não informou as horas, calcular automaticamente
                if (!isset($validated['horas_trabalhadas']) || $validated['horas_trabalhadas'] === null) {
                    $dailyReport->horas_trabalhadas = $dailyReport->calcularHorasTrabalhadasAutomaticamente();
                }

                $dailyReport->save();
            });

            return redirect()->route('daily-reports.index')
                ->with('success', 'Daily report criado com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao criar daily report', [
                'exception' => $e,
                'user_id' => $user->id ?? null,
                'data' => $validated ?? [],
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Erro ao criar daily report. Tente novamente.'])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(DailyReport $dailyReport): Response
    {
        $this->authorize('view', $dailyReport);

        $dailyReport->load(['projeto', 'usuario']);

        return Inertia::render('DailyReports/Show', [
            'dailyReport' => $dailyReport,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DailyReport $dailyReport): Response
    {
        // Verificar se o usuário tem permissão para editar este daily report
        if ($dailyReport->usuario_id !== Auth::id()) {
            abort(403, 'Você não tem permissão para editar este daily report.');
        }

        $hoje = now()->startOfDay();
        $dataReport = $dailyReport->data->startOfDay();
        if ($dataReport->diffInDays($hoje, false) < 0 || $dataReport->diffInDays($hoje, false) > 1) {
            abort(403, 'Só é possível editar o daily report no mesmo dia ou no dia seguinte.');
        }

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

        return Inertia::render('DailyReports/Edit', [
            'dailyReport' => $dailyReport,
            'projetosAtivos' => $projetosAtivos,
            'horasPorProjetoPorDia' => $horasPorProjetoPorDia,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DailyReportRequest $request, DailyReport $dailyReport): RedirectResponse
    {
        if ($dailyReport->usuario_id !== Auth::id()) {
            abort(403, 'Você não tem permissão para editar este daily report.');
        }

        $hoje = now()->startOfDay();
        $dataReport = $dailyReport->data->startOfDay();
        if ($dataReport->diffInDays($hoje, false) < 0 || $dataReport->diffInDays($hoje, false) > 1) {
            abort(403, 'Só é possível editar o daily report no mesmo dia ou no dia seguinte.');
        }

        $validated = $request->validated();

        // Verificar se já existe outro daily report para esta data (se a data foi alterada)
        if ($dailyReport->data->format('Y-m-d') !== $validated['data']) {
            $existente = DailyReport::where('usuario_id', Auth::id())
                ->where('data', $validated['data'])
                ->where('id', '!=', $dailyReport->id)
                ->first();

            if ($existente) {
                return redirect()->back()
                    ->withErrors(['data' => 'Você já possui um daily report para esta data.'])
                    ->withInput();
            }
        }

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
            DB::transaction(function () use ($validated, $dailyReport) {
                $dailyReport->fill($validated);

                // Se não informou as horas, recalcular automaticamente
                if (!isset($validated['horas_trabalhadas']) || $validated['horas_trabalhadas'] === null) {
                    $dailyReport->horas_trabalhadas = $dailyReport->calcularHorasTrabalhadasAutomaticamente();
                }

                $dailyReport->save();
            });

            return redirect()->route('daily-reports.index')
                ->with('success', 'Daily report atualizado com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Erro ao atualizar daily report. Tente novamente.'])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DailyReport $dailyReport): RedirectResponse
    {
        // Verificar se o usuário tem permissão para deletar este daily report
        if ($dailyReport->usuario_id !== Auth::id()) {
            abort(403, 'Você não tem permissão para deletar este daily report.');
        }

        $hoje = now()->startOfDay();
        $dataReport = $dailyReport->data->startOfDay();
        if ($dataReport->diffInDays($hoje, false) < 0 || $dataReport->diffInDays($hoje, false) > 1) {
            abort(403, 'Só é possível remover o daily report no mesmo dia ou no dia seguinte.');
        }

        try {
            $dailyReport->forceDelete();

            return redirect()->route('daily-reports.index')
                ->with('success', 'Daily report removido com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Erro ao remover daily report. Tente novamente.']);
        }
    }
}
