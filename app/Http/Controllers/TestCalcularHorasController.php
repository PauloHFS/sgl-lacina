<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TestCalcularHorasController extends Controller
{
    public function test()
    {
        // Simular um usuário logado
        $user = \App\Models\User::first();
        if (!$user) {
            return response()->json(['error' => 'Nenhum usuário encontrado']);
        }

        Auth::login($user);

        // Simular o cálculo de horas para hoje
        $data = now()->format('Y-m-d');

        $dailyReport = new \App\Models\DailyReport(['data' => $data]);
        $dailyReport->usuario_id = $user->id;

        $horas = $dailyReport->calcularHorasTrabalhadasAutomaticamente();

        return response()->json([
            'usuario_id' => $user->id,
            'data' => $data,
            'dia_da_semana' => now()->format('l'),
            'horas_trabalhadas' => $horas,
            'horarios_count' => $user->horarios()->count()
        ]);
    }
}
