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
    Route::post('/profile/update', [ProfileController::class, 'completarCadastro'])->name('profile.completarCadastro');
});

// Rotas Autenticadas e Verificadas
Route::middleware(['auth', 'verified', 'posCadastroNecessario'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Rotas de Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rota para a página de horários (mock)
    Route::get('/meus-horarios', function () {
        // Mock de dados de autenticação, substitua pelo real quando integrado
        $mockAuthUser = new class {
            public $user;
            public function __construct()
            {
                $this->user = (object)['name' => 'Usuário Mock', 'email' => 'mock@example.com']; // Simula um usuário logado
            }
        };
        return Inertia::render('Horarios/MeuHorario', [
            'auth' => $mockAuthUser, // Passa o mock de autenticação para o componente
        ]);
    })->name('horarios.meus');


    // TODO: Mergear essas duas rotas
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::prefix('/projeto')->group(function () {
        Route::get('/', [ProjetosController::class, 'index'])->name('projetos.index');

        Route::get('/new', [ProjetosController::class, 'create'])->name('projetos.create');
        Route::post('/new', [ProjetosController::class, 'store'])->name('projetos.store');

        Route::get('/{projeto}', [ProjetosController::class, 'show'])->name('projetos.show');
        // Route::get('/{projeto}/edit', [ProjetosController::class, 'edit'])->name('projetos.edit');
    });

    // Rotas para Solicitação de Vínculo a Projeto
    Route::post('/vinculo', [ProjetoVinculoController::class, 'create'])->name('vinculo.create');

    Route::post('/relatorio/participacao', [RelatorioController::class, 'enviarRelatorioParticipacao'])->name('relatorio.participacao.enviar');

    // Rotas Específicas para Coordenadores
    Route::middleware('validarTipoVinculo:coordenador')->group(function () {
        Route::get('/colaboradores', [ColaboradorController::class, 'index'])->name('colaboradores.index');
        // A rota POST /colaboradores original apontava para ColaboradorController::aceitar sem um parâmetro {colaborador}.
        // O método aceitar requer um User $colaborador. A rota /colaboradores/{colaborador}/aceitar já serve a este propósito.
        // Route::post('/colaboradores', [ColaboradorController::class, 'aceitar'])->name('colaboradores.store');
        Route::get('/colaboradores/{id}', [ColaboradorController::class, 'show'])->name('colaboradores.show');
        Route::put('/colaboradores/{colaborador}', [ColaboradorController::class, 'update'])->name('colaboradores.update');

        Route::get('/validar-pre-candidato/{id}', [ColaboradorController::class, 'showValidateUsuario'])->name('colaboradores.showValidateUsuario');

        // TODO: Concestrar esse sebozeira aqui mergeando as rotas
        Route::post('/colaboradores/{colaborador}/aceitar', [ColaboradorController::class, 'aceitar'])->name('colaboradores.aceitar');
        Route::post('/colaboradores/{colaborador}/recusar', [ColaboradorController::class, 'recusar'])->name('colaboradores.recusar');

        // TODO : Concestrar esse sebozeira aqui mergeando as rotas
        Route::patch('/vinculo/{id}', [ProjetoVinculoController::class, 'update'])->name('vinculo.update');
        Route::prefix('/vinculos')->group(function () {
            Route::put('/{id}', [ProjetoVinculoController::class, 'update'])->name('vinculos.update');

            // TODO: Concestrar esse sebozeira aqui mergeando as rotas
            Route::post('/{colaborador}/aceitar', [ColaboradorController::class, 'aceitarVinculo'])->name('vinculos.aceitar');
            Route::post('/{colaborador}/recusar', [ColaboradorController::class, 'recusarVinculo'])->name('vinculos.recusar');
        });
    });
});

require __DIR__ . '/auth.php';
