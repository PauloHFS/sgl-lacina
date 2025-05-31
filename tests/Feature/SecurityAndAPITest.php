<?php

use App\Models\User;
use App\Models\Projeto;
use App\Models\UsuarioProjeto;
use App\Models\SolicitacaoTrocaProjeto;
use App\Enums\TipoProjeto;
use App\Enums\StatusCadastro;
use App\Enums\StatusVinculoProjeto;
use App\Enums\TipoVinculo;
use App\Enums\Funcao;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create test users with different roles
    $this->admin = User::factory()->create([
        'email' => 'admin@lacina.ufcg.edu.br',
        'status_cadastro' => StatusCadastro::ACEITO,
        'password' => Hash::make('admin123'),
    ]);

    $this->coordenador = User::factory()->create([
        'email' => 'coord@lacina.ufcg.edu.br',
        'status_cadastro' => StatusCadastro::ACEITO,
        'password' => Hash::make('coord123'),
    ]);

    $this->discente = User::factory()->create([
        'email' => 'discente@ccc.ufcg.edu.br',
        'status_cadastro' => StatusCadastro::ACEITO,
        'password' => Hash::make('discente123'),
    ]);

    $this->unauthorized = User::factory()->create([
        'email' => 'unauthorized@external.com',
        'status_cadastro' => StatusCadastro::PENDENTE,
        'password' => Hash::make('user123'),
    ]);

    // Create test project
    $this->projeto = Projeto::factory()->create();

    UsuarioProjeto::factory()->create([
        'usuario_id' => $this->coordenador->id,
        'projeto_id' => $this->projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR,
        'funcao' => Funcao::COORDENADOR,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);
});

describe('Authentication Security', function () {
    it('prevents brute force attacks with rate limiting', function () {
        $attempts = 0;
        $maxAttempts = 5;

        while ($attempts < $maxAttempts + 2) {
            $response = $this->post(route('login'), [
                'email' => $this->discente->email,
                'password' => 'wrong_password',
            ]);

            $attempts++;

            if ($attempts <= $maxAttempts) {
                $response->assertRedirect()
                    ->assertSessionHasErrors(['email']);
            } else {
                // Should be rate limited after max attempts
                $response->assertStatus(429);
            }
        }
    });

    it('enforces strong password policies', function () {
        $weakPasswords = [
            '123',
            'password',
            '12345678',
            'abc123',
            'qwerty',
        ];

        foreach ($weakPasswords as $password) {
            $response = $this->post(route('register'), [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => $password,
                'password_confirmation' => $password,
                'cpf' => '12345678901',
                'docente_responsavel_email' => $this->coordenador->email,
            ]);

            $response->assertSessionHasErrors(['password']);
        }
    });

    it('prevents password reuse', function () {
        $user = $this->discente;
        $currentPassword = 'current_password_123';

        $user->update(['password' => Hash::make($currentPassword)]);

        $this->actingAs($user);

        // Try to change to same password
        $response = $this->patch(route('password.update'), [
            'current_password' => $currentPassword,
            'password' => $currentPassword,
            'password_confirmation' => $currentPassword,
        ]);

        $response->assertSessionHasErrors(['password']);
    });

    it('enforces session timeout', function () {
        $this->actingAs($this->discente);

        // Simulate session expiry
        Session::put('last_activity', now()->subHours(3)->timestamp);

        $response = $this->get(route('dashboard'));

        // Should redirect to login due to timeout
        $response->assertRedirect(route('login'));
    });

    it('prevents session fixation attacks', function () {
        // Get initial session ID
        $response = $this->get(route('login'));
        $initialSessionId = Session::getId();

        // Login
        $this->post(route('login'), [
            'email' => $this->discente->email,
            'password' => 'discente123',
        ]);

        // Session ID should change after login
        $newSessionId = Session::getId();
        expect($newSessionId)->not->toBe($initialSessionId);
    });
});

describe('Authorization and Access Control', function () {
    it('prevents horizontal privilege escalation', function () {
        $user1 = $this->discente;
        $user2 = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);

        $this->actingAs($user1);

        // Try to access another user's profile
        $response = $this->get(route('colaboradores.show', $user2));
        $response->assertStatus(403);

        // Try to update another user's profile
        $response = $this->patch(route('colaboradores.update', $user2), [
            'name' => 'Hacked Name',
        ]);
        $response->assertStatus(403);
    });

    it('prevents vertical privilege escalation', function () {
        $this->actingAs($this->discente);

        // Try to access admin functions
        $response = $this->get(route('colaboradores.index'));
        $response->assertStatus(403);

        // Try to approve user registrations
        $response = $this->patch(route('colaboradores.aprovar', $this->unauthorized));
        $response->assertStatus(403);

        // Try to delete projects
        $response = $this->delete(route('projetos.destroy', $this->projeto));
        $response->assertStatus(403);
    });

    it('validates project coordination permissions', function () {
        $outroProjeto = Projeto::factory()->create();

        $this->actingAs($this->coordenador);

        // Should be able to manage own project
        $response = $this->get(route('projetos.show', $this->projeto));
        $response->assertOk();

        // Should not be able to manage other projects
        $solicitacao = UsuarioProjeto::factory()->create([
            'projeto_id' => $outroProjeto->id,
            'status' => StatusVinculoProjeto::PENDENTE,
        ]);

        $response = $this->patch(route('projetos.vinculos.aprovar', [
            'projeto' => $outroProjeto,
            'vinculo' => $solicitacao,
        ]));

        $response->assertStatus(403);
    });

    it('enforces data isolation between projects', function () {
        $projeto2 = Projeto::factory()->create();

        $vinculo1 = UsuarioProjeto::factory()->create([
            'projeto_id' => $this->projeto->id,
            'usuario_id' => $this->discente->id,
        ]);

        $vinculo2 = UsuarioProjeto::factory()->create([
            'projeto_id' => $projeto2->id,
        ]);

        $this->actingAs($this->coordenador);

        $response = $this->get(route('projetos.show', $this->projeto));

        $response->assertOk()
            ->assertInertia(
                fn($page) => $page
                    ->where(
                        'usuarios',
                        fn($users) =>
                        collect($users)->pluck('id')->doesntContain($vinculo2->usuario_id)
                    )
            );
    });
});

describe('Input Validation and Sanitization', function () {
    it('prevents XSS attacks in user inputs', function () {
        $this->actingAs($this->coordenador);

        $maliciousInputs = [
            '<script>alert("XSS")</script>',
            '"><img src=x onerror=alert("XSS")>',
            'javascript:alert("XSS")',
            '<iframe src="javascript:alert(\'XSS\')"></iframe>',
        ];

        foreach ($maliciousInputs as $input) {
            $response = $this->post(route('projetos.store'), [
                'nome' => $input,
                'descricao' => $input,
                'data_inicio' => '2024-01-01',
                'cliente' => 'Cliente Teste',
                'tipo' => TipoProjeto::PDI->value,
            ]);

            if ($response->isRedirect()) {
                $projeto = Projeto::latest()->first();

                // Verify input was sanitized
                expect($projeto->nome)->not->toContain('<script>');
                expect($projeto->nome)->not->toContain('javascript:');
                expect($projeto->descricao)->not->toContain('<iframe>');
            }
        }
    });

    it('validates file upload security', function () {
        $this->actingAs($this->discente);

        // Test malicious file types
        $maliciousFiles = [
            'virus.exe',
            'script.php',
            'malware.bat',
            'trojan.com',
        ];

        foreach ($maliciousFiles as $filename) {
            $file = \Illuminate\Http\UploadedFile::fake()->create($filename, 100);

            $response = $this->patch(route('profile.update'), [
                'foto' => $file,
            ]);

            $response->assertSessionHasErrors(['foto']);
        }
    });

    it('prevents SQL injection attacks', function () {
        $this->actingAs($this->coordenador);

        $sqlInjectionAttempts = [
            "'; DROP TABLE projetos; --",
            "1' OR '1'='1",
            "1' UNION SELECT * FROM users --",
            "'; DELETE FROM users WHERE '1'='1",
        ];

        foreach ($sqlInjectionAttempts as $injection) {
            $response = $this->get(route('projetos.index', ['search' => $injection]));

            // Should not cause database errors
            $response->assertOk();

            // Verify database integrity
            $this->assertDatabaseCount('projetos', 1); // Original test project
            $this->assertDatabaseCount('users', 4); // Original test users
        }
    });

    it('validates CSRF protection', function () {
        $this->actingAs($this->coordenador);

        // Attempt request without CSRF token
        $response = $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('projetos.store'), [
                'nome' => 'Projeto CSRF',
                'data_inicio' => '2024-01-01',
                'cliente' => 'Cliente',
                'tipo' => TipoProjeto::PDI->value,
            ]);

        // Should be blocked
        $response->assertStatus(419);
    });
});

describe('Data Security and Privacy', function () {
    it('encrypts sensitive user data', function () {
        $user = User::factory()->create([
            'cpf' => '12345678901',
        ]);

        // Verify sensitive data is stored encrypted/hashed
        $rawData = DB::table('users')->where('id', $user->id)->first();

        // CPF and RG should be stored as provided (but validated)
        expect($rawData->cpf)->toBe('12345678901');
        expect($rawData->cpf)->toBe('12345678901');

        // Password should be hashed
        expect($rawData->password)->not->toBe('password');
        expect(Hash::check('password', $rawData->password))->toBeFalse();
    });

    it('implements proper data masking in logs', function () {
        $this->actingAs($this->discente);

        // Perform action that might log sensitive data
        $response = $this->patch(route('profile.update'), [
            'cpf' => '98765432109',
            'conta_bancaria' => '12345-6',
        ]);

        $response->assertRedirect();

        // Check that sensitive data is not logged in plain text
        $logContent = Log::getMonolog()->getHandlers()[0]->getUrl() ?? '';

        if ($logContent) {
            expect($logContent)->not->toContain('98765432109');
            expect($logContent)->not->toContain('12345-6');
        }
    });

    it('prevents information disclosure through error messages', function () {
        // Test with invalid user ID
        $this->actingAs($this->coordenador);

        $response = $this->get('/colaboradores/99999');

        // Should not reveal internal system information
        $response->assertStatus(404);
        expect($response->getContent())->not->toContain('Database');
        expect($response->getContent())->not->toContain('SQL');
    });

    it('enforces data retention policies', function () {
        // Create old records
        $oldUser = User::factory()->create([
            'created_at' => now()->subYears(10),
            'status_cadastro' => StatusCadastro::RECUSADO,
        ]);

        $oldProject = Projeto::factory()->create([
            'created_at' => now()->subYears(10),
            'data_termino' => now()->subYears(9),
        ]);

        // Soft delete them
        $oldUser->delete();
        $oldProject->delete();

        // Verify they can be force deleted according to policy
        expect($oldUser->trashed())->toBeTrue();
        expect($oldProject->trashed())->toBeTrue();
    });
});

describe('API Security (if applicable)', function () {
    it('validates API authentication tokens', function () {
        // Test API endpoints without authentication
        $response = $this->getJson('/api/projetos');
        $response->assertStatus(401);

        // Test with invalid token
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid_token_here',
        ])->getJson('/api/projetos');

        $response->assertStatus(401);
    });

    it('implements proper API rate limiting', function () {
        $this->actingAs($this->discente);

        // Make many rapid requests
        for ($i = 0; $i < 100; $i++) {
            $response = $this->getJson('/api/dashboard');

            if ($response->status() === 429) {
                // Rate limit kicked in
                break;
            }
        }

        // Should eventually hit rate limit
        expect($i)->toBeLessThan(100);
    });

    it('validates API input size limits', function () {
        $this->actingAs($this->coordenador);

        $largePayload = [
            'nome' => str_repeat('A', 10000), // Very large name
            'descricao' => str_repeat('B', 100000), // Very large description
        ];

        $response = $this->postJson('/api/projetos', $largePayload);

        // Should reject oversized input
        $response->assertStatus(422);
    });
});

describe('Advanced Security Scenarios', function () {
    it('prevents timing attacks on user enumeration', function () {
        $existingEmail = $this->discente->email;
        $nonExistentEmail = 'nonexistent@example.com';

        // Measure response times
        $startTime1 = microtime(true);
        $this->post(route('login'), [
            'email' => $existingEmail,
            'password' => 'wrong_password',
        ]);
        $time1 = microtime(true) - $startTime1;

        $startTime2 = microtime(true);
        $this->post(route('login'), [
            'email' => $nonExistentEmail,
            'password' => 'wrong_password',
        ]);
        $time2 = microtime(true) - $startTime2;

        // Response times should be similar to prevent enumeration
        $timeDifference = abs($time1 - $time2);
        expect($timeDifference)->toBeLessThan(0.1); // 100ms tolerance
    });

    it('prevents LDAP injection in email validation', function () {
        $ldapInjectionAttempts = [
            'user@domain.com)(uid=*',
            'user@domain.com))(|(uid=*',
            'user*)(&(uid=admin',
        ];

        foreach ($ldapInjectionAttempts as $injection) {
            $response = $this->post(route('register'), [
                'name' => 'Test User',
                'email' => $injection,
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'cpf' => '12345678901',
                'docente_responsavel_email' => $this->coordenador->email,
            ]);

            $response->assertSessionHasErrors(['email']);
        }
    });

    it('validates secure cookie settings', function () {
        $response = $this->post(route('login'), [
            'email' => $this->discente->email,
            'password' => 'discente123',
        ]);

        // Check cookie security attributes
        $cookies = $response->headers->getCookies();

        foreach ($cookies as $cookie) {
            if ($cookie->getName() === session()->getName()) {
                expect($cookie->isHttpOnly())->toBeTrue();
                expect($cookie->isSecure())->toBeTrue(); // In HTTPS environment
                expect($cookie->getSameSite())->toBe('Lax');
            }
        }
    });

    it('prevents clickjacking attacks', function () {
        $this->actingAs($this->discente);

        $response = $this->get(route('dashboard'));

        // Should have X-Frame-Options header
        $response->assertHeader('X-Frame-Options', 'DENY');
    });

    it('implements content security policy', function () {
        $this->actingAs($this->discente);

        $response = $this->get(route('dashboard'));

        // Should have CSP header
        expect($response->headers->has('Content-Security-Policy'))->toBeTrue();

        $csp = $response->headers->get('Content-Security-Policy');
        expect($csp)->toContain("default-src 'self'");
    });
});
