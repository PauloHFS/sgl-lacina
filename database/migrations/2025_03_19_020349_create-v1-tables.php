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
        DB::statement("CREATE TYPE tipo_vinculo AS ENUM ('ALUNO_GRADUACAO', 'ALUNO_MESTRADO', 'ALUNO_DOUTORADO', 'PROFISSIONAL')");
        DB::statement("CREATE TYPE status_participacao_projeto AS ENUM ('APROVADO', 'PENDENTE', 'REJEITADO')");
        DB::statement("CREATE TYPE status_solicitacao_troca_projeto AS ENUM ('PENDENTE', 'APROVADO', 'REJEITADO')");
        DB::statement("CREATE TYPE week_day AS ENUM ('SEGUNDA', 'TERCA', 'QUARTA', 'QUINTA', 'SEXTA', 'SABADO', 'DOMINGO')");
        DB::statement("CREATE TYPE tipo_horario AS ENUM ('AULA', 'TRABALHO', 'AUSENTE')");
        DB::statement("CREATE TYPE tipo_folga AS ENUM ('COLETIVA', 'INDIVIDUAL')");
        DB::statement("CREATE TYPE status_folga AS ENUM ('PENDENTE', 'APROVADO', 'REJEITADO')");

        Schema::create('colaboradores', function (Blueprint $table) {
            $table->foreignId('id')->constrained('users')->onDelete('cascade')->primary();
            $table->string('linkedin')->nullable();
            $table->string('github')->nullable();
            $table->string('figma')->nullable();
            $table->string('foto')->nullable();
            $table->string('curriculo')->nullable();
            $table->string('areas_atuacao')->nullable();
            $table->string('tecnologias')->nullable();
            $table->string('cpf')->unique();
            $table->string('rg')->unique();
            $table->string('uf_rg')->nullable();
            $table->string('orgao_emissor')->nullable();
            $table->string('conta_bancaria')->nullable();
            $table->string('agencia')->nullable();
            $table->string('banco')->nullable();
            $table->string('telefone')->nullable();
            $table->timestampsTz();
        });

        Schema::create('colaborador_vinculo', function (Blueprint $table) {
            $table->foreignId('colaborador_id')->constrained('users');
            $table->string('tipo_vinculo')->type('tipo_vinculo');
            $table->dateTime('data_inicio');
            $table->dateTime('data_fim')->nullable();

            $table->primary(['colaborador_id', 'data_fim']);
        });

        Schema::create('docentes', function (Blueprint $table) {
            $table->foreignId('id')->constrained('colaboradores')->primary();
            $table->timestampsTz();
        });

        Schema::create('projetos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('descricao')->nullable();
            $table->timestamptz('data_inicio');
            $table->timestamptz('data_fim')->nullable();
            $table->string('cliente');
            $table->string('slack')->nullable();
            $table->string('discord')->nullable();
            $table->string('board')->nullable();
            $table->string('tipo')->nullable();
            $table->timestampsTz();
        });

        Schema::create('docente_projeto', function (Blueprint $table) {
            $table->foreignId('docente_id')->constrained('docentes');
            $table->foreignId('projeto_id')->constrained('projetos');
            $table->timestampsTz();

            $table->primary(['docente_id', 'projeto_id']);
        });

        Schema::create('participacao_projeto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('colaborador_id')->constrained('colaboradores');
            $table->foreignId('projeto_id')->constrained('projetos');
            $table->timestampTz('data_inicio');
            $table->timestampTz('data_fim')->nullable();
            $table->integer('carga_horaria_semanal');
            $table->string('status_participacao_projeto')->type('status_participacao_projeto');
            $table->timestampsTz();
        });

        Schema::create('solicitacoes_troca_projeto', function (Blueprint $table) {
            $table->foreignId('colaboradores_id')->constrained('colaboradores');
            $table->foreignId('projeto_atual_id')->constrained('projetos');
            $table->foreignId('projeto_novo_id')->constrained('projetos');
            $table->string('motivo');
            $table->string('resposta')->nullable();
            $table->string('status')->type('status_participacao_projeto');
            $table->timestampTz('data_solicitacao');
            $table->timestampTz('data_resposta')->nullable();
            $table->timestampsTz();
            $table->string('status')->type('status_participacao_projeto');


            $table->primary(['colaboradores_id', 'projeto_atual_id', 'projeto_novo_id']);
        });

        Schema::create('horarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('colaborador_id')->constrained('colaboradores');
            $table->string('dia_semana')->type('week_day');
            $table->string('tipo')->type('tipo_horario');
            $table->time('hora_inicio');
            $table->time('hora_fim');
            $table->timestampsTz();
        });

        Schema::create('folgas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('colaborador_id')->constrained('colaboradores');
            $table->string('tipo')->type('tipo_folga');
            $table->string('status')->type('status_folga');
            $table->string('dia_semana')->type('week_day');
            $table->timestampTz('data_inicio');
            $table->timestampTz('data_fim');
            $table->string('justificativa');
            $table->timestampsTz();
        });

        Schema::create('salas', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('senha_porta')->nullable();
            $table->timestampsTz();
        });

        Schema::create('baias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sala_id')->constrained('salas');
            $table->string('nome');
            $table->timestampsTz();
        });

        Schema::create('horario_baia', function (Blueprint $table) {
            $table->foreignId('horario_id')->constrained('horarios');
            $table->foreignId('baia_id')->constrained('baias');
            $table->timestampsTz();

            $table->primary(['horario_id', 'baia_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop tables in reverse order to respect foreign key constraints
        Schema::dropIfExists('horario_baia');
        Schema::dropIfExists('baias');
        Schema::dropIfExists('salas');
        Schema::dropIfExists('folgas');
        Schema::dropIfExists('horarios');
        Schema::dropIfExists('solicitacoes_troca_projeto');
        Schema::dropIfExists('participacao_projeto');
        Schema::dropIfExists('docente_projeto');
        Schema::dropIfExists('projetos');
        Schema::dropIfExists('docentes');
        Schema::dropIfExists('colaborador_vinculo');
        Schema::dropIfExists('colaboradores');

        // Drop enum types
        DB::statement('DROP TYPE IF EXISTS tipo_vinculo');
        DB::statement('DROP TYPE IF EXISTS status_participacao_projeto');
        DB::statement('DROP TYPE IF EXISTS status_solicitacao_troca_projeto');
        DB::statement('DROP TYPE IF EXISTS week_day');
        DB::statement('DROP TYPE IF EXISTS tipo_horario');
        DB::statement('DROP TYPE IF EXISTS tipo_folga');
        DB::statement('DROP TYPE IF EXISTS status_folga');
    }
};
