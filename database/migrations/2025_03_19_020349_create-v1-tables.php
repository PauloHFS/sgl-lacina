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
        $this->createEnumIfNotExists('tipo_vinculo', ['ALUNO_GRADUACAO', 'ALUNO_MESTRADO', 'ALUNO_DOUTORADO', 'PROFISSIONAL']);
        $this->createEnumIfNotExists('status_participacao_projeto', ['APROVADO', 'PENDENTE', 'REJEITADO']);
        $this->createEnumIfNotExists('status_solicitacao_troca_projeto', ['PENDENTE', 'APROVADO', 'REJEITADO']);
        $this->createEnumIfNotExists('week_day', ['SEGUNDA', 'TERCA', 'QUARTA', 'QUINTA', 'SEXTA', 'SABADO', 'DOMINGO']);
        $this->createEnumIfNotExists('tipo_horario', ['AULA', 'TRABALHO', 'AUSENTE']);
        $this->createEnumIfNotExists('tipo_folga', ['COLETIVA', 'INDIVIDUAL']);
        $this->createEnumIfNotExists('status_folga', ['PENDENTE', 'APROVADO', 'REJEITADO']);

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
            $table->id();
            $table->foreignId('participacao_projeto_id')->constrained('participacao_projeto');
            $table->foreignId('projeto_novo_id')->constrained('projetos');
            $table->text('motivo');
            $table->text('resposta')->nullable();
            $table->string('status');
            $table->date('data_resposta')->nullable();

            $table->timestamps();
        });
        DB::statement('ALTER TABLE solicitacoes_troca_projeto ALTER COLUMN status TYPE status_solicitacao_troca_projeto USING status::status_solicitacao_troca_projeto');

        Schema::create('horarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('colaborador_id')->constrained('colaboradores');
            $table->string('dia_semana');
            $table->time('horario_inicio');
            $table->time('horario_termino');
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
        Schema::dropIfExists('participacao_projeto');
        Schema::dropIfExists('docente_projeto');
        Schema::dropIfExists('projetos');
        Schema::dropIfExists('docentes');
        Schema::dropIfExists('colaborador_vinculo');
        Schema::dropIfExists('colaboradores');

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
