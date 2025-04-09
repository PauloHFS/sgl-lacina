<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->createEnumIfNotExists('tipo_vinculo', ['COORDENADOR', 'COLABORADOR']);
        $this->createEnumIfNotExists('funcao', ['COODERNADOR', 'PESQUISADOR', 'DESENVOLVEDOR', 'TECNICO', 'ALUNO']);

        Schema::create('usuario_vinculo', function (Blueprint $table) {
            $table->foreignId('projeto_id')->constrained('projetos');
            $table->foreignId('usuario_id')->constrained('users');
            $table->string('tipo_vinculo'); // Definimos como string primeiro
            $table->string('funcao'); // Definimos como string primeiro
            $table->dateTime('data_inicio');
            $table->dateTime('data_fim')->nullable();

            $table->primary(['usuario_id', 'data_fim']);
        });

        // Alterar o tipo da coluna usando SQL direto
        DB::statement('ALTER TABLE usuario_vinculo ALTER COLUMN tipo_vinculo TYPE tipo_vinculo USING tipo_vinculo::tipo_vinculo');
        DB::statement('ALTER TABLE usuario_vinculo ALTER COLUMN funcao TYPE funcao USING funcao::funcao');

        // >>>>>>

        $this->createEnumIfNotExists('tipo_projeto', ['PDI', 'TCC', 'MESTRADO', 'DOUTORADO', 'SUPORTE']);

        Schema::create('projetos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->date('data_inicio');
            $table->date('data_termino')->nullable();
            $table->string('cliente');
            $table->text('descricao')->nullable();
            $table->string('slack_url')->nullable();
            $table->string('discord_url')->nullable();
            $table->string('board_url')->nullable();
            $table->string('git_url')->nullable();
            $table->string('tipo'); // Definimos como string primeiro
            $table->timestamps();
        });

        // Alterar o tipo da coluna usando SQL direto
        DB::statement('ALTER TABLE projetos ALTER COLUMN tipo TYPE tipo_projeto USING tipo::tipo_projeto');

        // >>>>>>

        $this->createEnumIfNotExists('status_participacao_projeto', ['APROVADO', 'PENDENTE', 'REJEITADO']);

        Schema::create('solicitacoes_projeto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios');
            $table->foreignId('projeto_id')->constrained('projetos');
            $table->dateTime('data_inicio');
            $table->dateTime('data_fim')->nullable();
            $table->integer('carga_horaria');
            $table->string('status');
            $table->timestamps();
        });
        DB::statement('ALTER TABLE participacao_projeto ALTER COLUMN status TYPE status_participacao_projeto USING status::status_participacao_projeto');

        // >>>>>>

        $this->createEnumIfNotExists('status_solicitacao_troca_projeto', ['PENDENTE', 'APROVADO', 'REJEITADO']);

        Schema::create('solicitacoes_troca_projeto', function (Blueprint $table) {
            $table->foreignId('usuario_id')->constrained('usuarios');
            $table->foreignId('projeto_atual_id')->constrained('projetos');
            $table->foreignId('projeto_novo_id')->constrained('projetos');
            $table->text('motivo');
            $table->text('resposta')->nullable();
            $table->string('status'); // Definimos como string primeiro
            $table->date('data_solicitacao');
            $table->date('data_resposta')->nullable();
            $table->timestamps();

            $table->primary(['usuario_id', 'projeto_atual_id', 'data_solicitacao']);
        });

        // Alterar o tipo da coluna usando SQL direto
        DB::statement('ALTER TABLE solicitacoes_troca_projeto ALTER COLUMN status TYPE status_solicitacao_troca_projeto USING status::status_solicitacao_troca_projeto');

        // >>>>>>

        $this->createEnumIfNotExists('week_day', ['SEGUNDA', 'TERCA', 'QUARTA', 'QUINTA', 'SEXTA', 'SABADO', 'DOMINGO']);
        $this->createEnumIfNotExists('tipo_horario', ['AULA', 'TRABALHO', 'AUSENTE']);

        Schema::create('horarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios');
            $table->string('dia_semana'); // Definimos como string primeiro
            $table->time('horario_inicio');
            $table->time('horario_termino');
            $table->string('tipo'); // Definimos como string primeiro
            $table->timestamps();
        });

        // Alterar o tipo da coluna usando SQL direto
        DB::statement('ALTER TABLE horarios ALTER COLUMN dia_semana TYPE week_day USING dia_semana::week_day');
        DB::statement('ALTER TABLE horarios ALTER COLUMN tipo TYPE tipo_horario USING tipo::tipo_horario');

        // >>>>>>

        $this->createEnumIfNotExists('tipo_folga', ['COLETIVA', 'INDIVIDUAL']);
        $this->createEnumIfNotExists('status_folga', ['PENDENTE', 'APROVADO', 'REJEITADO']);

        Schema::create('folgas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios');
            $table->string('tipo'); // Definimos como string primeiro
            $table->string('status'); // Definimos como string primeiro
            $table->date('data_inicio');
            $table->date('data_fim');
            $table->text('justificativa')->nullable();
            $table->timestamps();
        });

        // Alterar o tipo da coluna usando SQL direto
        DB::statement('ALTER TABLE folgas ALTER COLUMN tipo TYPE tipo_folga USING tipo::tipo_folga');
        DB::statement('ALTER TABLE folgas ALTER COLUMN status TYPE status_folga USING status::status_folga');

        // >>>>>>

        // TODO Validar isso aqui ainda
        Schema::create('salas', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('senha_porta')->nullable();
            $table->timestamps();
        });

        Schema::create('baias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sala_id')->constrained('salas');
            $table->string('nome');
            $table->timestamps();
        });

        Schema::create('horario_baia', function (Blueprint $table) {
            $table->foreignId('horario_id')->constrained('horarios');
            $table->foreignId('baia_id')->constrained('baias');

            $table->primary(['horario_id', 'baia_id']);
            $table->timestamps();
        });

        // >>>>>>
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop tabelas em ordem reversa para respeitar constraints
        Schema::dropIfExists('horario_baia');
        Schema::dropIfExists('baias');
        Schema::dropIfExists('salas');
        Schema::dropIfExists('folgas');
        Schema::dropIfExists('horarios');
        Schema::dropIfExists('solicitacoes_troca_projeto');
        Schema::dropIfExists('solicitacoes_projeto');
        Schema::dropIfExists('projetos');
        Schema::dropIfExists('usuario_vinculo');

        // Drop types enum
        Schema::dropIfExists('status_folga');
        Schema::dropIfExists('tipo_folga');
        Schema::dropIfExists('tipo_horario');
        Schema::dropIfExists('week_day');
        Schema::dropIfExists('status_solicitacao_troca_projeto');
        Schema::dropIfExists('status_participacao_projeto');
        Schema::dropIfExists('tipo_vinculo');
    }

    private function createEnumIfNotExists($name, $values)
    {
        $typeExists = DB::select("SELECT 1 FROM pg_type WHERE typname = ?", [$name]);

        if (empty($typeExists)) {
            $valuesString = implode("', '", $values);
            DB::statement("CREATE TYPE $name AS ENUM ('$valuesString')");
        }
    }
};
