<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Models\Sala;
use App\Models\Baia;

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

        return Inertia::render('Salas/Show', [
            'sala' => $sala,
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

        // Atualizar dados da sala
        $sala->update($request->only(['nome', 'descricao', 'ativa']));

        // Lidar com baias deletadas
        if ($request->filled('baias_deletadas')) {
            $sala->baias()->whereIn('id', $request->baias_deletadas)->delete();
        }

        // Processar baias (criar novas e atualizar existentes)
        if ($request->filled('baias')) {
            foreach ($request->baias as $baiaData) {
                if (isset($baiaData['id'])) {
                    // Atualizar baia existente
                    $baia = $sala->baias()->find($baiaData['id']);
                    if ($baia) {
                        $baia->update($baiaData);
                    }
                } else {
                    // Criar nova baia
                    $sala->baias()->create([
                        'nome' => $baiaData['nome'],
                        'descricao' => $baiaData['descricao'] ?? null,
                        'ativa' => $baiaData['ativa'] ?? true,
                    ]);
                }
            }
        }

        Log::info('Sala e baias atualizadas', [
            'user_id' => $request->user()->id,
            'sala_id' => $sala->id,
            'data' => $request->all(),
        ]);

        return redirect()->route('salas.show', $sala->id)->with('success', 'Sala atualizada com sucesso!');
    }

    public function destroy(Request $request, $id)
    {
        $sala = Sala::findOrFail($id);

        // Deletar as baias primeiro devido ao relacionamento
        $sala->baias()->delete();
        $sala->delete();

        return redirect()->route('salas.index')->with('success', 'Sala excluída com sucesso!');
    }
}
