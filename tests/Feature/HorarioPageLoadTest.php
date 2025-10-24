<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('página de edição de horários carrega corretamente', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/horarios/edit');

    $response->assertStatus(200)
        ->assertInertia(
            fn(Assert $page) => $page
                ->component('Horarios/Edit')
                ->has('horarios')
                ->has('salas')
        );
});

test('página de edição de horários não é acessível sem autenticação', function () {
    $response = $this->get('/horarios/edit');

    $response->assertRedirect('/login');
});
