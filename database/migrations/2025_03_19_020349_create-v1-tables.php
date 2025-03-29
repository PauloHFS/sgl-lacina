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
        // Cria os tipos enum
        DB::statement("CREATE TYPE tipo_vinculo AS ENUM ('ALUNO_GRADUACAO', 'ALUNO_MESTRADO', 'ALUNO_DOUTORADO', 'PROFISSIONAL')");
        DB::statement("CREATE TYPE status_participacao_projeto AS ENUM ('APROVADO', 'PENDENTE', 'REJEITADO')");
        DB::statement("CREATE TYPE status_solicitacao_troca_projeto AS ENUM ('PENDENTE', 'APROVADO', 'REJEITADO')");
        DB::statement("CREATE TYPE week_day AS ENUM ('SEGUNDA', 'TERCA', 'QUARTA', 'QUINTA', 'SEXTA', 'SABADO', 'DOMINGO')");
        DB::statement("CREATE TYPE tipo_horario AS ENUM ('AULA', 'TRABALHO', 'AUSENTE')");
        DB::statement("CREATE TYPE tipo_folga AS ENUM ('COLETIVA', 'INDIVIDUAL')");
        DB::statement("CREATE TYPE status_folga AS ENUM ('PENDENTE', 'APROVADO', 'REJEITADO')");

        // Tabela colaboradores - ajustado conforme DBML
        Schema::create('colaboradores', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->foreign('id')->references('id')->on('users')->onDelete('cascade');

            $table->string('linkedin')->nullable();
            $table->string('github')->nullable();
            $table->string('figma')->nullable();
            $table->string('foto')->nullable();
            $table->text('curriculo')->nullable(); // Mudado para text
            $table->string('area_atuacao')->nullable(); // Corrigido (era areas_atuacao)
            $table->text('tecnologias')->nullable(); // Mudado para text
            $table->string('cpf')->unique();
            $table->string('rg')->unique();
            $table->string('uf_rg')->nullable();
            $table->string('conta_bancaria')->nullable();
            $table->string('agencia')->nullable();
            $table->string('codigo_banco')->nullable(); // Corrigido (era banco)
            $table->string('telefone')->nullable();
            $table->timestamps(); // Standard Laravel timestamps
        });

        // Tabela colaborador_vinculo
        Schema::create('colaborador_vinculo', function (Blueprint $table) {
            $table->foreignId('colaborador_id')->constrained('colaboradores');
            $table->string('tipo_vinculo'); // Definimos como string primeiro
            $table->dateTime('data_inicio');
            $table->dateTime('data_fim')->nullable();

            $table->primary(['colaborador_id', 'data_fim']);
        });
        // Alterar o tipo da coluna usando SQL direto
        DB::statement('ALTER TABLE colaborador_vinculo ALTER COLUMN tipo_vinculo TYPE tipo_vinculo USING tipo_vinculo::tipo_vinculo');

        Schema::create('docentes', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->foreign('id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('projetos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->date('data_inicio');
            $table->date('data_termino')->nullable(); // Mudado conforme DBML
            $table->string('cliente');
            $table->string('link_slack')->nullable(); // Corrigido (era slack)
            $table->string('link_discord')->nullable(); // Corrigido (era discord)
            $table->string('link_board')->nullable(); // Corrigido (era board)
            $table->string('tipo')->nullable();
            $table->timestamps();
        });

        Schema::create('docente_projeto', function (Blueprint $table) {
            $table->foreignId('docente_id')->constrained('docentes');
            $table->foreignId('projeto_id')->constrained('projetos');

            $table->primary(['docente_id', 'projeto_id']);
            $table->timestamps();
        });

        Schema::create('participacao_projeto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('colaborador_id')->constrained('colaboradores');
            $table->foreignId('projeto_id')->constrained('projetos');
            $table->dateTime('data_inicio');
            $table->dateTime('data_fim')->nullable();
            $table->integer('carga_horaria');
            $table->string('status');
            $table->timestamps();
        });
        DB::statement('ALTER TABLE participacao_projeto ALTER COLUMN status TYPE status_participacao_projeto USING status::status_participacao_projeto');

        Schema::create('solicitacoes_troca_projeto', function (Blueprint $table) {
            $table->foreignId('colaborador_id')->constrained('colaboradores'); // Corrigido (era colaboradores_id)
            $table->foreignId('projeto_atual_id')->constrained('projetos');
            $table->foreignId('projeto_novo_id')->constrained('projetos');
            $table->text('motivo');
            $table->text('resposta')->nullable();
            $table->string('status');
            $table->date('data_solicitacao');
            $table->date('data_resposta')->nullable();

            $table->primary(['colaborador_id', 'projeto_atual_id', 'projeto_novo_id']);
            $table->timestamps();
        });
        DB::statement('ALTER TABLE solicitacoes_troca_projeto ALTER COLUMN status TYPE status_solicitacao_troca_projeto USING status::status_solicitacao_troca_projeto');

        Schema::create('horarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('colaborador_id')->constrained('colaboradores');
            $table->string('dia_semana');
            $table->time('horario_inicio'); // Corrigido (era hora_inicio)
            $table->time('horario_termino'); // Corrigido (era hora_fim)
            $table->string('tipo');
            $table->timestamps();
        });
        DB::statement('ALTER TABLE horarios ALTER COLUMN dia_semana TYPE week_day USING dia_semana::week_day');
        DB::statement('ALTER TABLE horarios ALTER COLUMN tipo TYPE tipo_horario USING tipo::tipo_horario');

        Schema::create('folgas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('colaborador_id')->constrained('colaboradores');
            $table->string('tipo');
            $table->string('status');
            $table->date('data_inicio');
            $table->date('data_fim');
            $table->text('justificativa')->nullable();
            $table->timestamps();
        });
        DB::statement('ALTER TABLE folgas ALTER COLUMN tipo TYPE tipo_folga USING tipo::tipo_folga');
        DB::statement('ALTER TABLE folgas ALTER COLUMN status TYPE status_folga USING status::status_folga');

        Schema::create('salas', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('senha_porta');
            $table->timestamps();
        });

        Schema::create('baias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sala_id')->constrained('salas');
            $table->string('nome');
            $table->timestamps();
        });

        Schema::create('horarios_baia', function (Blueprint $table) { // Corrigido (era horario_baia)
            $table->foreignId('horario_id')->constrained('horarios');
            $table->foreignId('sala_id')->constrained('salas'); // Ajustado para sala_id conforme DBML

            $table->primary(['horario_id', 'sala_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop tabelas em ordem reversa para respeitar constraints
        Schema::dropIfExists('horarios_baia');
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

        // Drop types enum
        DB::statement('DROP TYPE IF EXISTS tipo_vinculo');
        DB::statement('DROP TYPE IF EXISTS status_participacao_projeto');
        DB::statement('DROP TYPE IF EXISTS status_solicitacao_troca_projeto');
        DB::statement('DROP TYPE IF EXISTS week_day');
        DB::statement('DROP TYPE IF EXISTS tipo_horario');
        DB::statement('DROP TYPE IF EXISTS tipo_folga');
        DB::statement('DROP TYPE IF EXISTS status_folga');
    }
};
