<?php

use App\Enums\StatusCadastro;
use App\Enums\StatusVinculoProjeto;
use App\Enums\TipoVinculo;
use App\Enums\Funcao;
use App\Enums\TipoProjeto;
use App\Enums\Genero;
use App\Models\User;
use App\Models\Projeto;
use App\Models\UsuarioProjeto;
use App\Models\Banco;
use Illuminate\Support\Facades\Validator;

test('validação de CPF deve aceitar formato correto', function () {
    $validator = Validator::make(
        ['cpf' => '12345678901'],
        ['cpf' => 'regex:/^\d{11}$/']
    );

    expect($validator->passes())->toBeTrue();
});

test('validação de CPF deve rejeitar formatos incorretos', function () {
    $formatosInvalidos = [
        '123.456.789-01', // com pontuação
        '12345678900', // 10 dígitos
        '123456789012', // 12 dígitos
        'abcdefghijk', // letras
        '', // vazio
    ];

    foreach ($formatosInvalidos as $cpfInvalido) {
        $validator = Validator::make(
            ['cpf' => $cpfInvalido],
            ['cpf' => 'regex:/^\d{11}$/']
        );

        expect($validator->fails())->toBeTrue();
    }
});

test('validação de CEP deve aceitar formato correto', function () {
    $validator = Validator::make(
        ['cep' => '12345678'],
        ['cep' => 'regex:/^\d{8}$/']
    );

    expect($validator->passes())->toBeTrue();
});

test('validação de CEP deve rejeitar formatos incorretos', function () {
    $formatosInvalidos = [
        '12345-678', // com hífen
        '1234567', // 7 dígitos
        '123456789', // 9 dígitos
        'abcdefgh', // letras
        '', // vazio
    ];

    foreach ($formatosInvalidos as $cepInvalido) {
        $validator = Validator::make(
            ['cep' => $cepInvalido],
            ['cep' => 'regex:/^\d{8}$/']
        );

        expect($validator->fails())->toBeTrue();
    }
});

test('validação de data de nascimento deve aceitar datas válidas', function () {
    $datasValidas = [
        '1990-01-01',
        '2000-12-31',
        '1985-06-15',
    ];

    foreach ($datasValidas as $data) {
        $validator = Validator::make(
            ['data_nascimento' => $data],
            ['data_nascimento' => 'date_format:Y-m-d']
        );

        expect($validator->passes())->toBeTrue();
    }
});

test('validação de data de nascimento deve rejeitar formatos incorretos', function () {
    $formatosInvalidos = [
        '01/01/1990', // formato brasileiro
        '1990-1-1', // sem zeros à esquerda
        '32-01-1990', // dia inválido
        '1990-13-01', // mês inválido
        'abc', // não é data
        '', // vazio
    ];

    foreach ($formatosInvalidos as $dataInvalida) {
        $validator = Validator::make(
            ['data_nascimento' => $dataInvalida],
            ['data_nascimento' => 'date_format:Y-m-d']
        );

        expect($validator->fails())->toBeTrue();
    }
});

test('validação de UF deve aceitar estados válidos', function () {
    $ufsValidas = ['SP', 'RJ', 'MG', 'RS', 'PR', 'SC', 'PB', 'PE', 'BA'];

    foreach ($ufsValidas as $uf) {
        $validator = Validator::make(
            ['uf' => $uf],
            ['uf' => 'size:2']
        );

        expect($validator->passes())->toBeTrue();
    }
});

test('validação de UF deve rejeitar formatos incorretos', function () {
    $formatosInvalidos = [
        'São Paulo', // nome completo
        'SP1', // 3 caracteres
        'S', // 1 caractere
        'sp', // minúsculas (dependendo da validação)
        '', // vazio
    ];

    foreach ($formatosInvalidos as $ufInvalida) {
        $validator = Validator::make(
            ['uf' => $ufInvalida],
            ['uf' => 'size:2']
        );

        expect($validator->fails())->toBeTrue();
    }
});

test('validação de telefone deve aceitar formatos básicos', function () {
    $telefonesValidos = [
        '(11) 99999-9999',
        '11999999999',
        '+5511999999999',
        '11 99999-9999',
    ];

    foreach ($telefonesValidos as $telefone) {
        $validator = Validator::make(
            ['telefone' => $telefone],
            ['telefone' => 'string|max:255']
        );

        expect($validator->passes())->toBeTrue();
    }
});

test('validação de email deve aceitar formatos válidos', function () {
    $emailsValidos = [
        'usuario@example.com',
        'test.email@domain.co.uk',
        'user+tag@gmail.com',
        'student@computacao.ufcg.edu.br',
    ];

    foreach ($emailsValidos as $email) {
        $validator = Validator::make(
            ['email' => $email],
            ['email' => 'email']
        );

        expect($validator->passes())->toBeTrue();
    }
});

test('validação de email deve rejeitar formatos inválidos', function () {
    $emailsInvalidos = [
        'usuario',
        'usuario@',
        '@domain.com',
        'usuario..email@domain.com',
        'usuario@domain',
        '',
    ];

    foreach ($emailsInvalidos as $email) {
        $validator = Validator::make(
            ['email' => $email],
            ['email' => 'email']
        );

        expect($validator->fails())->toBeTrue();
    }
});

test('validação de URLs deve aceitar formatos válidos', function () {
    $urlsValidas = [
        'https://github.com/user',
        'https://linkedin.com/in/user',
        'http://example.com',
        'https://www.figma.com/file/123',
    ];

    foreach ($urlsValidas as $url) {
        $validator = Validator::make(
            ['url' => $url],
            ['url' => 'url']
        );

        expect($validator->passes())->toBeTrue();
    }
});

test('validação de URLs deve rejeitar formatos inválidos', function () {
    $urlsInvalidas = [
        'github.com/user', // sem protocolo
        'not-a-url',
        'http://',
        'https://',
        '',
    ];

    foreach ($urlsInvalidas as $url) {
        $validator = Validator::make(
            ['url' => $url],
            ['url' => 'url']
        );

        expect($validator->fails())->toBeTrue();
    }
});

test('validação de carga horária deve aceitar valores válidos', function () {
    $cargasValidas = [1, 8, 20, 40, 44];

    foreach ($cargasValidas as $carga) {
        $validator = Validator::make(
            ['carga_horaria_semanal' => $carga],
            ['carga_horaria_semanal' => 'integer|min:1|max:44']
        );

        expect($validator->passes())->toBeTrue();
    }
});

test('validação de carga horária deve rejeitar valores inválidos', function () {
    $cargasInvalidas = [0, -1, 45, 100, 'abc'];

    foreach ($cargasInvalidas as $carga) {
        $validator = Validator::make(
            ['carga_horaria_semanal' => $carga],
            ['carga_horaria_semanal' => 'integer|min:1|max:44']
        );

        expect($validator->fails())->toBeTrue();
    }
});

test('validação de data de término deve ser posterior à data de início', function () {
    $validator = Validator::make(
        [
            'data_inicio' => '2024-01-01',
            'data_termino' => '2024-12-31',
        ],
        [
            'data_inicio' => 'required|date',
            'data_termino' => 'nullable|date|after_or_equal:data_inicio',
        ]
    );

    expect($validator->passes())->toBeTrue();
});

test('validação de data de término deve rejeitar data anterior ao início', function () {
    $validator = Validator::make(
        [
            'data_inicio' => '2024-12-31',
            'data_termino' => '2024-01-01',
        ],
        [
            'data_inicio' => 'required|date',
            'data_termino' => 'nullable|date|after_or_equal:data_inicio',
        ]
    );

    expect($validator->fails())->toBeTrue();
});

test('validação de enums deve aceitar valores válidos', function () {
    $enumsValidos = [
        ['field' => 'status_cadastro', 'value' => StatusCadastro::ACEITO->value],
        ['field' => 'tipo_vinculo', 'value' => TipoVinculo::COLABORADOR->value],
        ['field' => 'funcao', 'value' => Funcao::DESENVOLVEDOR->value],
        ['field' => 'status_vinculo', 'value' => StatusVinculoProjeto::APROVADO->value],
        ['field' => 'tipo_projeto', 'value' => TipoProjeto::PDI->value],
        ['field' => 'genero', 'value' => Genero::MASCULINO->value],
    ];

    foreach ($enumsValidos as $enumTest) {
        $field = $enumTest['field'];
        $value = $enumTest['value'];

        $validator = Validator::make(
            [$field => $value],
            [$field => 'string']
        );

        expect($validator->passes())->toBeTrue();
    }
});

test('validação de campos únicos deve funcionar corretamente', function () {
    $user1 = User::factory()->cadastroCompleto()->create([
        'email' => 'user1@example.com',
        'cpf' => '12345678901',
    ]);

    // Tentar criar usuário com mesmo email
    $validator = Validator::make(
        ['email' => 'user1@example.com'],
        ['email' => 'unique:users,email']
    );

    expect($validator->fails())->toBeTrue();

    // Tentar criar usuário com mesmo CPF
    $validator = Validator::make(
        ['cpf' => '12345678901'],
        ['cpf' => 'unique:users,cpf']
    );

    expect($validator->fails())->toBeTrue();
});

test('validação de campos obrigatórios deve funcionar corretamente', function () {
    $camposObrigatorios = [
        'name' => '',
        'email' => '',
        'password' => '',
    ];

    $validator = Validator::make(
        $camposObrigatorios,
        [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
        ]
    );

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('name'))->toBeTrue();
    expect($validator->errors()->has('email'))->toBeTrue();
    expect($validator->errors()->has('password'))->toBeTrue();
});

test('validação de relacionamentos deve verificar existência', function () {
    $banco = Banco::factory()->create();

    // Banco existente
    $validator = Validator::make(
        ['banco_id' => $banco->id],
        ['banco_id' => 'exists:bancos,id']
    );

    expect($validator->passes())->toBeTrue();

    // Banco inexistente
    $validator = Validator::make(
        ['banco_id' => 'non-existent-id'],
        ['banco_id' => 'exists:bancos,id']
    );

    expect($validator->fails())->toBeTrue();
});

test('validação de JSONB deve aceitar JSON válido', function () {
    $jsonValido = [
        'Matricula' => '123456789',
        'Chave Dell' => 'ABC123',
        'Chave Microsoft' => 'DEF456',
    ];

    $validator = Validator::make(
        ['campos_extras' => json_encode($jsonValido)],
        ['campos_extras' => 'json']
    );

    expect($validator->passes())->toBeTrue();
});

test('validação de JSONB deve rejeitar JSON inválido', function () {
    $jsonInvalido = '{"invalid": json}';

    $validator = Validator::make(
        ['campos_extras' => $jsonInvalido],
        ['campos_extras' => 'json']
    );

    expect($validator->fails())->toBeTrue();
});

test('validação deve funcionar com múltiplas regras combinadas', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'email' => 'existing@example.com',
    ]);

    $dados = [
        'name' => 'João Silva',
        'email' => 'new@example.com',
        'cpf' => '98765432100',
        'telefone' => '(11) 99999-9999',
        'data_nascimento' => '1990-01-01',
        'cep' => '12345678',
        'uf' => 'SP',
    ];

    $regras = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'cpf' => 'nullable|regex:/^\d{11}$/|unique:users,cpf',
        'telefone' => 'nullable|string|max:255',
        'data_nascimento' => 'nullable|date_format:Y-m-d',
        'cep' => 'nullable|regex:/^\d{8}$/',
        'uf' => 'nullable|size:2',
    ];

    $validator = Validator::make($dados, $regras);

    expect($validator->passes())->toBeTrue();
});

test('validação de atualização deve ignorar próprio registro', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'email' => 'user@example.com',
        'cpf' => '12345678901',
    ]);

    // Atualizar próprio usuário deve passar
    $validator = Validator::make(
        ['email' => 'user@example.com'],
        ['email' => "unique:users,email,{$user->id}"]
    );

    expect($validator->passes())->toBeTrue();
});
