<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class HorarioController extends Controller
{

    public function index(Request $request)
    {
        $horarios = $request->user()->horarios()
            ->with([
                'usuarioProjeto.projeto:id,nome',
                'baia:id,nome,sala_id',
                'baia.sala:id,nome'
            ])
            ->orderBy('dia_da_semana')
            ->orderBy('horario', 'asc')
            ->get()
            ->groupBy('dia_da_semana');

        Log::info('Listando horários do usuário', [
            'user_id' => $request->user()->id,
            'horarios_count' => $horarios->count(),
            'horarios' => $horarios,
        ]);

        return Inertia::render('Horarios/Index', [
            'horarios' => $horarios,
        ]);
    }

    public function update(Request $request, $id)
    {
        // Lógica para atualizar o horário com o ID fornecido
        // Exemplo: Validar e atualizar o horário no banco de dados

        return redirect()->back()->with('success', 'Horário atualizado com sucesso!');
    }
}
