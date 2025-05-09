<?php

use App\Http\Controllers\ColaboradorController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjetoVinculoController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\Banco;
use App\Http\Controllers\ProjetosController;

// Rotas Públicas
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});


Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/pos-cadastro', function () {
        $bancos = Banco::all();

        return Inertia::render('PosCadastro', [
            'bancos' => $bancos,
        ]);
    })->name('pos-cadastro');
    Route::post('/profile/update', [ProfileController::class, 'completarCadastro'])->name('profile.completarCadastro');
});

// Rotas Autenticadas e Verificadas
Route::middleware(['auth', 'verified', 'posCadastroNecessario'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/projetos', [ProjetosController::class, 'index'])->name('projetos.index');
    Route::get('/projetos/create', [ProjetosController::class, 'create'])->name('projetos.create'); // New route for displaying form
    Route::post('/projetos', [ProjetosController::class, 'store'])->name('projetos.store'); // New route for storing project

    // Rotas de Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // TODO: Mergear essas duas rotas
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Rotas para Solicitação de Vínculo a Projeto
    Route::post('/projetos/{projeto}/solicitar-vinculo', [ProjetoVinculoController::class, 'solicitarVinculo'])
        ->name('projetos.solicitar-vinculo');

    // Rotas Específicas para Coordenadores
    Route::middleware('validarTipoVinculo:coordenador')->group(function () {
        Route::get('/colaboradores', [ColaboradorController::class, 'index'])->name('colaboradores.index');
        // A rota POST /colaboradores original apontava para ColaboradorController::aceitar sem um parâmetro {colaborador}.
        // O método aceitar requer um User $colaborador. A rota /colaboradores/{colaborador}/aceitar já serve a este propósito.
        // Route::post('/colaboradores', [ColaboradorController::class, 'aceitar'])->name('colaboradores.store');
        Route::get('/validar-pre-candidato/{id}', [ColaboradorController::class, 'showValidateUsuario'])->name('colaboradores.showValidateUsuario');
        Route::get('/colaboradores/{id}', [ColaboradorController::class, 'show'])->name('colaboradores.show');

        // TODO: Concestrar esse sebozeira aqui mergeando as rotas
        Route::post('/colaboradores/{colaborador}/aceitar', [ColaboradorController::class, 'aceitar'])->name('colaboradores.aceitar');
        Route::post('/colaboradores/{colaborador}/recusar', [ColaboradorController::class, 'recusar'])->name('colaboradores.recusar');

        // TODO: Concestrar esse sebozeira aqui mergeando as rotas
        Route::post('/vinculos/{colaborador}/aceitar', [ColaboradorController::class, 'aceitarVinculo'])
            ->name('vinculos.aceitar');
        Route::post('/vinculos/{colaborador}/recusar', [ColaboradorController::class, 'recusarVinculo'])
            ->name('vinculos.recusar');
    });
});

require __DIR__ . '/auth.php';
