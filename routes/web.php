<?php

use App\Http\Controllers\ColaboradorController;
use App\Http\Controllers\ProfileController;
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

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/pos-cadastro', function () {
    return Inertia::render('PosCadastro');
})->middleware(['auth', 'verified'])->name('pos-cadastro');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/colaboradores', [ColaboradorController::class, 'index'])->name('colaboradores.index');
    Route::get('/validar-pre-candidato/{id}', [ColaboradorController::class, 'showValidateUsuario'])->name('colaboradores.showValidateUsuario');
});

require __DIR__ . '/auth.php';
