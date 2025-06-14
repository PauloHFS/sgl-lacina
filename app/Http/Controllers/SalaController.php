<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Models\Sala;

class SalaController extends Controller
{

    public function index(Request $request)
    {
        $salas = Sala::with('baia')->paginate(10);

        return Inertia::render('Salas/Index', [
            'salas' => $salas,
            'canCreate' => $request->user()->can('create', Sala::class),
            'canEdit' => $request->user()->can('update', Sala::class),
            'canDelete' => $request->user()->can('delete', Sala::class),
        ]);
    }

    public function show(Request $request, $id)
    {
        $sala = Sala::findOrFail($id)->load('baia');

        return Inertia::render('Salas/Show', [
            'sala' => $sala,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string|max:1000',
            'capacidade' => 'required|integer|min:1',
        ]);

        Sala::create($request->all());

        Log::info('Criando nova sala', [
            'user_id' => $request->user()->id,
            'data' => $request->all(),
        ]);

        return redirect()->back()->with('success', 'Sala criada com sucesso!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string|max:1000',
        ]);

        $sala = Sala::findOrFail($id);
        $sala->update($request->all());

        return redirect()->back()->with('success', 'Sala atualizada com sucesso!');
    }

    public function destroy(Request $request, $id)
    {
        $sala = Sala::findOrFail($id);
        $sala->delete();

        return redirect()->back()->with('success', 'Sala excluída com sucesso!');
    }
}
