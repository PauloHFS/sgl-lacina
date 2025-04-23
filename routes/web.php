<?php

use App\Http\Controllers\ColaboradorController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjetoVinculoController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/pos-cadastro', function () {
    return Inertia::render('PosCadastro');
})->middleware(['auth', 'verified'])->name('pos-cadastro');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::post('/profile/update', [ProfileController::class, 'completarCadastro'])->name('profile.completarCadastro')->middleware(['auth', 'verified']);

Route::middleware(['auth', 'verified', 'checkUserRole:coordenador'])->group(function () {
    Route::get('/colaboradores', [ColaboradorController::class, 'index'])->name('colaboradores.index');
    Route::post('/colaboradores', [ColaboradorController::class, 'aceitar'])->name('colaboradores.store');
    Route::get('/validar-pre-candidato/{id}', [ColaboradorController::class, 'showValidateUsuario'])->name('colaboradores.showValidateUsuario');
    Route::get('/colaboradores/{id}', [ColaboradorController::class, 'show'])->name('colaboradores.show');

    Route::post('/colaboradores/{colaborador}/aceitar', [ColaboradorController::class, 'aceitar'])->name('colaboradores.aceitar');
    Route::post('/colaboradores/{colaborador}/recusar', [ColaboradorController::class, 'recusar'])->name('colaboradores.recusar');

    Route::post('/vinculos/{colaborador}/aceitar', [ColaboradorController::class, 'aceitarVinculo'])
        ->name('vinculos.aceitar');
    Route::post('/vinculos/{colaborador}/recusar', [ColaboradorController::class, 'recusarVinculo'])
        ->name('vinculos.recusar');
});

Route::post('/projetos/{projeto}/solicitar-vinculo', [ProjetoVinculoController::class, 'solicitarVinculo'])
    ->name('projetos.solicitar-vinculo')
    ->middleware(['auth']);


require __DIR__ . '/auth.php';
