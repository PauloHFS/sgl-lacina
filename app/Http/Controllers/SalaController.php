<?php

namespace App\Http\Controllers;

use App\Enums\DiaDaSemana;
use App\Enums\TipoHorario;
use App\Models\Horario;
use App\Models\Sala;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SalaController extends Controller
{
    public function index(Request $request)
    {
        // Validação dos parâmetros de busca
        $request->validate([
            'search' => 'nullable|string|max:255',
            'page' => 'nullable|integer|min:1',
        ]);

        $query = Sala::with(['baias' => function ($query) {
            $query->select('id', 'sala_id', 'nome', 'ativa');
        }]);

        // Busca por nome ou descrição
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nome', 'ILIKE', "%{$search}%")
                    ->orWhere('descricao', 'ILIKE', "%{$search}%");
            });
        }

        // Ordenação padrão
        $query->orderBy('nome');

        $salas = $query->paginate(10)
            ->withQueryString()
            ->through(function ($sala) {
                return [
                    'id' => $sala->id,
                    'nome' => $sala->nome,
                    'descricao' => $sala->descricao,
                    'ativa' => $sala->ativa,
                    'baias' => $sala->baias->map(function ($baia) {
                        return [
                            'id' => $baia->id,
                            'nome' => $baia->nome,
                            'ativa' => $baia->ativa,
                        ];
                    }),
                    'created_at' => $sala->created_at,
                    'updated_at' => $sala->updated_at,
                ];
            });

        return Inertia::render('Salas/Index', [
            'salas' => $salas,
            'canCreate' => $request->user()->can('create', Sala::class),
            'canEdit' => $request->user()->can('updateAny', Sala::class),
            'canDelete' => $request->user()->can('deleteAny', Sala::class),
            'filters' => [
                'search' => $request->search,
            ],
        ]);
    }

    public function show(Request $request, $id)
    {
        $sala = Sala::with(['baias' => function ($query) {
            $query->orderBy('nome');
        }])->findOrFail($id);

        // Buscar horários da sala agrupados por dia e horário
        $horariosData = $this->getHorariosSala($sala);

        return Inertia::render('Salas/Show', [
            'sala' => $sala,
            'horarios' => $horariosData,
            'canEdit' => $request->user()->can('update', $sala),
            'canDelete' => $request->user()->can('delete', $sala),
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::render('Salas/Create');
    }

    public function edit(Request $request, $id)
    {
        $sala = Sala::with(['baias' => function ($query) {
            $query->orderBy('nome');
        }])->findOrFail($id);

        return Inertia::render('Salas/Edit', [
            'sala' => $sala,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string|max:1000',
            'ativa' => 'boolean',
            'baias' => 'array',
            'baias.*.nome' => 'required|string|max:255',
            'baias.*.descricao' => 'nullable|string|max:1000',
            'baias.*.ativa' => 'boolean',
        ]);

        $sala = Sala::create($request->only(['nome', 'descricao', 'ativa']));

        if ($request->filled('baias')) {
            foreach ($request->baias as $baiaData) {
                $sala->baias()->create([
                    'nome' => $baiaData['nome'],
                    'descricao' => $baiaData['descricao'] ?? null,
                    'ativa' => $baiaData['ativa'] ?? true,
                ]);
            }
        }

        return redirect()->route('salas.show', $sala->id)->with('success', 'Sala criada com sucesso!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string|max:1000',
            'ativa' => 'boolean',
            'baias' => 'array',
            'baias.*.nome' => 'required|string|max:255',
            'baias.*.descricao' => 'nullable|string|max:1000',
            'baias.*.ativa' => 'boolean',
            'baias_deletadas' => 'array',
            'baias_deletadas.*' => 'string',
        ]);

        $sala = Sala::findOrFail($id);

        $sala->update($request->only(['nome', 'descricao', 'ativa']));

        if ($request->filled('baias_deletadas')) {
            $sala->baias()->whereIn('id', $request->baias_deletadas)->delete();
        }

        if ($request->filled('baias')) {
            foreach ($request->baias as $baiaData) {
                if (isset($baiaData['id'])) {
                    $baia = $sala->baias()->find($baiaData['id']);
                    if ($baia) {
                        $baia->update($baiaData);
                    }
                } else {
                    $sala->baias()->create([
                        'nome' => $baiaData['nome'],
                        'descricao' => $baiaData['descricao'] ?? null,
                        'ativa' => $baiaData['ativa'] ?? true,
                    ]);
                }
            }
        }

        return redirect()->route('salas.show', $sala->id)->with('success', 'Sala atualizada com sucesso!');
    }

    public function destroy(Request $request, $id)
    {
        $sala = Sala::findOrFail($id);

        $sala->delete();

        return redirect()->route('salas.index')->with('success', 'Sala excluída com sucesso!');
    }

    private function getHorariosSala(Sala $sala)
    {
        // Buscar todas as baias da sala
        $baiaIds = $sala->baias->pluck('id');

        // Buscar horários onde pessoas estão trabalhando presencialmente nessas baias
        $horarios = Horario::with([
            'usuario:id,name,email,foto_url',
            'usuarioProjeto.projeto:id,nome',
            'baia:id,nome,sala_id',
        ])
            ->whereIn('baia_id', $baiaIds)
            ->where('tipo', TipoHorario::TRABALHO_PRESENCIAL)
            ->get();

        // Organizar os dados por dia da semana e horário
        $horariosAgrupados = [];

        foreach (DiaDaSemana::cases() as $dia) {
            $horariosAgrupados[$dia->value] = [];

            for ($hora = 7; $hora <= 20; $hora++) {
                $pessoasNoHorario = $horarios->filter(function ($horario) use ($dia, $hora) {
                    return $horario->dia_da_semana === $dia && $horario->horario === $hora;
                });

                $horariosAgrupados[$dia->value][$hora] = [
                    'count' => $pessoasNoHorario->count(),
                    'pessoas' => $pessoasNoHorario->map(function ($horario) {
                        return [
                            'id' => $horario->usuario->id,
                            'name' => $horario->usuario->name,
                            'email' => $horario->usuario->email,
                            'foto_url' => $horario->usuario->foto_url,
                            'baia' => $horario->baia ? [
                                'id' => $horario->baia->id,
                                'nome' => $horario->baia->nome,
                            ] : null,
                            'projeto' => $horario->usuarioProjeto?->projeto ? [
                                'id' => $horario->usuarioProjeto->projeto->id,
                                'nome' => $horario->usuarioProjeto->projeto->nome,
                            ] : null,
                        ];
                    })->values(),
                ];
            }
        }

        return $horariosAgrupados;
    }
}
