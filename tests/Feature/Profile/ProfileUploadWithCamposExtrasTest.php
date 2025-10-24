<?php

use App\Enums\StatusCadastro;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('pode fazer upload de foto com campos extras via formdata', function () {
  // Arrange
  Storage::fake('public');

  $user = User::factory()->cadastroCompleto()->create([
    'status_cadastro' => StatusCadastro::ACEITO,
    'email_verified_at' => now(),
    'campos_extras' => ['campo_existente' => 'valor_existente'],
  ]);

  $this->actingAs($user);

  $file = UploadedFile::fake()->image('nova_foto.jpg', 300, 300)->size(1024); // 1MB

  // Simular FormData com campos_extras como JSON string
  $formData = [
    'name' => 'Nome Atualizado Com Foto',
    'email' => 'novo@email.com',
    'telefone' => '85999887766',
    'area_atuacao' => 'Desenvolvimento Web',
    'foto_url' => $file,
    'campos_extras' => json_encode([
      'novo_campo' => 'novo_valor',
      'matricula' => '2024001234',
    ]),
    '_method' => 'PATCH',
  ];

  // Act
  $response = $this->post(route('profile.update'), $formData);

  // Assert
  $response->assertRedirect(route('profile.edit'));
  $response->assertSessionHas('status', 'Cadastro atualizado com sucesso!');

  // Verificar se os dados foram salvos corretamente
  $user->refresh();

  expect($user->name)->toBe('Nome Atualizado Com Foto');
  expect($user->email)->toBe('novo@email.com');
  expect($user->telefone)->toBe('85999887766');
  expect($user->area_atuacao)->toBe('Desenvolvimento Web');

  // Verificar se o arquivo foi armazenado
  expect($user->foto_url)->not->toBeNull();
  expect(Storage::disk('public')->exists($user->foto_url))->toBeTrue();

  // Verificar se os campos_extras foram processados corretamente
  expect($user->campos_extras)->toBeArray();
  expect($user->campos_extras['novo_campo'])->toBe('novo_valor');
  expect($user->campos_extras['matricula'])->toBe('2024001234');
});

test('campos extras vazio via formdata nao causa erro', function () {
  // Arrange
  Storage::fake('public');

  $user = User::factory()->cadastroCompleto()->create([
    'status_cadastro' => StatusCadastro::ACEITO,
    'email_verified_at' => now(),
  ]);

  $this->actingAs($user);

  $file = UploadedFile::fake()->image('nova_foto.jpg', 300, 300)->size(1024);

  $formData = [
    'name' => 'Nome Atualizado',
    'email' => 'novo@email.com',
    'foto_url' => $file,
    'campos_extras' => json_encode([]), // Objeto vazio
    '_method' => 'PATCH',
  ];

  // Act
  $response = $this->post(route('profile.update'), $formData);

  // Assert
  $response->assertRedirect(route('profile.edit'));
  $response->assertSessionHas('status', 'Cadastro atualizado com sucesso!');

  $user->refresh();
  expect($user->name)->toBe('Nome Atualizado');
  expect($user->foto_url)->not->toBeNull();
  expect(Storage::disk('public')->exists($user->foto_url))->toBeTrue();
});

test('campos extras invalido via formdata nao quebra aplicacao', function () {
  // Arrange
  Storage::fake('public');

  $user = User::factory()->cadastroCompleto()->create([
    'status_cadastro' => StatusCadastro::ACEITO,
    'email_verified_at' => now(),
  ]);

  $this->actingAs($user);

  $file = UploadedFile::fake()->image('nova_foto.jpg', 300, 300)->size(1024);

  $formData = [
    'name' => 'Nome Atualizado',
    'email' => 'novo@email.com',
    'foto_url' => $file,
    'campos_extras' => '{json_invalido', // JSON inválido
    '_method' => 'PATCH',
  ];

  // Act
  $response = $this->post(route('profile.update'), $formData);

  // Assert
  $response->assertRedirect(route('profile.edit'));
  $response->assertSessionHas('status', 'Cadastro atualizado com sucesso!');

  $user->refresh();
  expect($user->name)->toBe('Nome Atualizado');
  expect($user->foto_url)->not->toBeNull();
  expect(Storage::disk('public')->exists($user->foto_url))->toBeTrue();
});

test('upload sem campos extras continua funcionando', function () {
  // Arrange
  Storage::fake('public');

  $user = User::factory()->cadastroCompleto()->create([
    'status_cadastro' => StatusCadastro::ACEITO,
    'email_verified_at' => now(),
  ]);

  $this->actingAs($user);

  $file = UploadedFile::fake()->image('nova_foto.jpg', 300, 300)->size(1024);

  $formData = [
    'name' => 'Nome Atualizado Sem Campos',
    'email' => 'novo@email.com',
    'foto_url' => $file,
    '_method' => 'PATCH',
  ];

  // Act
  $response = $this->post(route('profile.update'), $formData);

  // Assert
  $response->assertRedirect(route('profile.edit'));
  $response->assertSessionHas('status', 'Cadastro atualizado com sucesso!');

  $user->refresh();
  expect($user->name)->toBe('Nome Atualizado Sem Campos');
  expect($user->foto_url)->not->toBeNull();
  expect(Storage::disk('public')->exists($user->foto_url))->toBeTrue();
});
