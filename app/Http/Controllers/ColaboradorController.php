<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Colaborador;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class ColaboradorController extends Controller
{
    public function index(Request $request)
    {
        Log::debug('Busca de colaboradores iniciada', [
            'search' => $request->input('search'),
        ]);

        $search = $request->input('search', '');

        if (!empty($search)) {
            $colaboradores = Colaborador::search($search)
                ->query(function ($builder) {
                    $builder->join('users', 'colaboradores.id', '=', 'users.id')
                        ->select('colaboradores.*', 'users.name', 'users.email');
                })
                ->paginate(10)
                ->appends($request->input('search'));
        } else {
            $colaboradoresQuery = Colaborador::query()
                ->join('users', 'colaboradores.id', '=', 'users.id')
                ->select('colaboradores.*', 'users.name', 'users.email');

            $colaboradores = $colaboradoresQuery
                ->orderBy('colaboradores.created_at', 'desc')
                ->paginate(10)
                ->appends($request->input('search'));
        }

        Log::debug('Busca de colaboradores concluída', [
            'total' => $colaboradores->total(),
        ]);

        return Inertia::render('Colaboradores/Index', [
            'colaboradores' => $colaboradores,
        ]);
    }
}
