<?php

use App\Enums\StatusCadastro;
use App\Models\User;
use App\Models\Projeto;
use App\Models\UsuarioProjeto;
use App\Enums\TipoVinculo;
use App\Enums\StatusVinculoProjeto;
use Illuminate\Support\Facades\Mail;

test('coordenador pode recusar cadastro de colaborador e o usuário é deletado', function () {
    Mail::fake();

    $coordenador = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projeto = Projeto::factory()->create();
    UsuarioProjeto::factory()->create([
        'usuario_id' => $coordenador->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    $colaborador = User::factory()->create([
        'status_cadastro' => StatusCadastro::PENDENTE,
        'name' => 'João Silva',
        'email' => 'joao@example.com',
    ]);

    $response = $this->actingAs($coordenador)
        ->post(route('colaboradores.recusar', $colaborador));

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Cadastro do colaborador recusado e removido do sistema com sucesso.');

    // Verificar que o usuário foi deletado
    expect(User::find($colaborador->id))->toBeNull();

    // Verificar que o email foi enviado
    Mail::assertSent(\App\Mail\CadastroRecusado::class, function ($mail) {
        return $mail->dadosColaborador['email'] === 'joao@example.com' &&
            $mail->dadosColaborador['name'] === 'João Silva';
    });
});

test('não é possível recusar colaborador que não está com cadastro pendente', function () {
    $coordenador = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projeto = Projeto::factory()->create();
    UsuarioProjeto::factory()->create([
        'usuario_id' => $coordenador->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    $colaborador = User::factory()->create([
        'status_cadastro' => StatusCadastro::ACEITO, // Já aceito
    ]);

    $response = $this->actingAs($coordenador)
        ->post(route('colaboradores.recusar', $colaborador));

    $response->assertRedirect();
    $response->assertSessionHas('error', 'Este colaborador não está com cadastro pendente.');

    // Verificar que o usuário NÃO foi deletado
    expect(User::find($colaborador->id))->not->toBeNull();
});

test('email de cadastro recusado contém informações corretas', function () {
    Mail::fake();

    $coordenador = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projeto = Projeto::factory()->create();
    UsuarioProjeto::factory()->create([
        'usuario_id' => $coordenador->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    $colaborador = User::factory()->create([
        'status_cadastro' => StatusCadastro::PENDENTE,
        'name' => 'Maria Santos',
        'email' => 'maria@test.com',
    ]);

    $this->actingAs($coordenador)
        ->post(route('colaboradores.recusar', $colaborador));

    Mail::assertSent(\App\Mail\CadastroRecusado::class, function ($mail) {
        return $mail->dadosColaborador['name'] === 'Maria Santos' &&
            $mail->dadosColaborador['email'] === 'maria@test.com' &&
            str_contains($mail->url, '/register');
    });
});

test('email de cadastro recusado inclui motivo quando fornecido', function () {
    Mail::fake();

    $coordenador = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projeto = Projeto::factory()->create();
    UsuarioProjeto::factory()->create([
        'usuario_id' => $coordenador->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    $colaborador = User::factory()->create([
        'status_cadastro' => StatusCadastro::PENDENTE,
        'name' => 'Carlos Silva',
        'email' => 'carlos@test.com',
    ]);

    $observacao = 'Candidato não possui experiência suficiente na área de atuação.';

    $this->actingAs($coordenador)
        ->post(route('colaboradores.recusar', $colaborador), [
            'observacao' => $observacao,
        ]);

    Mail::assertSent(\App\Mail\CadastroRecusado::class, function ($mail) use ($observacao) {
        return $mail->dadosColaborador['name'] === 'Carlos Silva' &&
            $mail->dadosColaborador['email'] === 'carlos@test.com' &&
            $mail->observacao === $observacao &&
            str_contains($mail->url, '/register');
    });
});
