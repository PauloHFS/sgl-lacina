<?php

use App\Http\Controllers\ColaboradorController;
use App\Http\Controllers\ConfiguracaoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjetoVinculoController;
use App\Http\Controllers\HorarioController;
use App\Http\Controllers\SalaController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\Banco;
use App\Http\Controllers\ProjetosController;
use App\Http\Controllers\RelatorioController;

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
    Route::post('/pos-cadastro', [ProfileController::class, 'completarCadastro'])->name('profile.completarCadastro');
});

// Rotas Autenticadas e Verificadas
Route::middleware(['auth', 'verified', 'posCadastroNecessario'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Rotas de Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::prefix("/horarios")->group(function () {
        Route::get('/', [HorarioController::class, 'index'])->name('horarios.index');
        Route::get('/edit', [HorarioController::class, 'edit'])->name('horarios.edit');
        Route::patch('/', [HorarioController::class, 'update'])->name('horarios.update');
        Route::get('/salas-disponiveis', [HorarioController::class, 'getSalasDisponiveis'])->name('horarios.salas-disponiveis');
        Route::get('/projetos-ativos', [HorarioController::class, 'getProjetosAtivos'])->name('horarios.projetos-ativos');
        Route::get('/colaborador/{colaborador}/projeto/{projeto}', [HorarioController::class, 'show'])->name('horarios.show');
    });

    Route::prefix("/salas")->group(function () {
        Route::get('/', [SalaController::class, 'index'])->name('salas.index');
        Route::get('/new', [SalaController::class, 'create'])->name('salas.create');
        Route::get('/{id}', [SalaController::class, 'show'])->name('salas.show');
        Route::get('/{id}/edit', [SalaController::class, 'edit'])->name('salas.edit');
        Route::post('/new', [SalaController::class, 'store'])->name('salas.store');
        Route::patch('/{id}', [SalaController::class, 'update'])->name('salas.update');
        Route::delete('/{id}', [SalaController::class, 'destroy'])->name('salas.destroy');
    });

    Route::prefix('/projeto')->group(function () {
        Route::get('/', [ProjetosController::class, 'index'])->name('projetos.index');

        Route::get('/new', [ProjetosController::class, 'create'])->name('projetos.create');
        Route::post('/new', [ProjetosController::class, 'store'])->name('projetos.store');

        Route::get('/{projeto}', [ProjetosController::class, 'show'])->name('projetos.show');
        Route::get('/{projeto}/edit', [ProjetosController::class, 'edit'])->name('projetos.edit');
        Route::patch('/{projeto}', [ProjetosController::class, 'update'])->name('projetos.update');
    });

    // Rotas para Solicitação de Vínculo a Projeto
    Route::post('/vinculo', [ProjetoVinculoController::class, 'create'])->name('vinculo.create');

    Route::post('/relatorio/participacao', [RelatorioController::class, 'enviarRelatorioParticipacao'])->name('relatorio.participacao.enviar');

    // Rotas Específicas para Coordenadores
    Route::middleware('validarTipoVinculo:coordenador')->group(function () {
        Route::prefix('/colaboradores')->group(function () {
            Route::get('/', [ColaboradorController::class, 'index'])->name('colaboradores.index');
            Route::get('/{id}', [ColaboradorController::class, 'show'])->name('colaboradores.show');
            Route::get('/{colaborador}/historico', [ColaboradorController::class, 'historico'])->name('colaboradores.historico');
            Route::put('/{colaborador}', [ColaboradorController::class, 'update'])->name('colaboradores.update');
            // TODO: Concestrar esse sebozeira aqui mergeando as rotas
            Route::post('/{colaborador}/aceitar', [ColaboradorController::class, 'aceitar'])->name('colaboradores.aceitar');
            Route::post('/{colaborador}/recusar', [ColaboradorController::class, 'recusar'])->name('colaboradores.recusar');
        });


        Route::get('/validar-pre-candidato/{id}', [ColaboradorController::class, 'showValidateUsuario'])->name('colaboradores.showValidateUsuario');

        // TODO : Concestrar esse sebozeira aqui mergeando as rotas
        Route::patch('/vinculo/{id}', [ProjetoVinculoController::class, 'update'])->name('vinculo.update');
        Route::prefix('/vinculos')->group(function () {
            Route::put('/{id}', [ProjetoVinculoController::class, 'update'])->name('vinculos.update');

            // TODO: Concestrar esse sebozeira aqui mergeando as rotas
            Route::post('/{colaborador}/aceitar', [ColaboradorController::class, 'aceitarVinculo'])->name('vinculos.aceitar');
            Route::post('/{colaborador}/recusar', [ColaboradorController::class, 'recusarVinculo'])->name('vinculos.recusar');
        });

        // Rotas para Configurações (apenas coordenadores)
        Route::prefix('/configuracoes')->name('configuracoes.')->group(function () {
            Route::get('/', [ConfiguracaoController::class, 'index'])->name('index');
            Route::patch('/senha-laboratorio', [ConfiguracaoController::class, 'atualizarSenhaLaboratorio'])->name('senha-laboratorio.update');
        });
    });
});

require __DIR__ . '/auth.php';
