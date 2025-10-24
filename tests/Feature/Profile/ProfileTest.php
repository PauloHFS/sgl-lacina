<?php

use App\Models\User;
use App\Models\Banco;

/**
 * Helper function to generate valid profile data
 */
function getValidProfileData(User $user, array $overrides = []): array
{
    // Primeiro tenta buscar um banco existente, se não houver, cria um
    $banco = Banco::first() ?? Banco::factory()->create();

    $validData = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'genero' => 'MASCULINO',
        'data_nascimento' => '1990-01-01',
        'cpf' => '11144477735',
        'rg' => '123456789',
        'uf_rg' => 'SP',
        'orgao_emissor_rg' => 'SSP',
        'cep' => '12345678',
        'endereco' => 'Rua Test, 123',
        'numero' => '123',
        'complemento' => 'Apt 1',
        'bairro' => 'Centro',
        'cidade' => 'São Paulo',
        'uf' => 'SP',
        'telefone' => '(11) 99999-9999',
        'conta_bancaria' => '123456789',
        'agencia' => '1234',
        'banco_id' => $banco->id,
        'curriculo_lattes_url' => 'http://lattes.cnpq.br/1234567890123456',
        'linkedin_url' => 'https://linkedin.com/in/test',
        'github_url' => 'https://github.com/test',
        'website_url' => 'https://test.com',
        'area_atuacao' => 'Desenvolvimento Web',
        'tecnologias' => 'PHP, Laravel, JavaScript',
        'campos_extras' => json_encode(['test' => 'value']),
    ];

    return array_merge($validData, $overrides);
}

test('profile page is displayed', function () {
    $user = User::factory()->cadastroCompleto()->create();

    $response = $this
        ->actingAs($user)
        ->get('/profile');

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->cadastroCompleto()->create();

    $updateData = getValidProfileData($user, [
        'name' => 'Test User Updated',
        'email' => 'testupdated@example.com',
        'cpf' => "11144477735",
        'rg' => $user->rg,
    ]);

    $response = $this
        ->actingAs($user)
        ->patch('/profile', $updateData);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    $this->assertSame('Test User Updated', $user->name);
    $this->assertSame('testupdated@example.com', $user->email);
    $this->assertNull($user->email_verified_at);
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->cadastroCompleto()->create();

    $updateData = getValidProfileData($user, [
        'name' => $user->name,
        'email' => $user->email,
        'cpf' => "11144477735",
        'rg' => $user->rg,
    ]);

    $response = $this
        ->actingAs($user)
        ->patch('/profile', $updateData);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $this->assertNotNull($user->refresh()->email_verified_at);
});

test('user can delete their account', function () {
    $user = User::factory()->cadastroCompleto()->create();

    $response = $this
        ->actingAs($user)
        ->delete('/profile', [
            'password' => 'Ab@12312',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
    $this->assertNull($user->fresh());
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->cadastroCompleto()->create();

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->delete('/profile', [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrors('password')
        ->assertRedirect('/profile');

    $this->assertNotNull($user->fresh());
});
