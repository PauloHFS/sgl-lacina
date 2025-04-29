<?php

use App\Enums\Funcao;
use App\Enums\StatusVinculoProjeto;
use App\Enums\TipoProjeto;
use App\Enums\TipoVinculo;
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
        Schema::create('projetos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->date('data_inicio');
            $table->date('data_termino')->nullable();
            $table->string('cliente');
            $table->string('slack_url')->nullable();
            $table->string('discord_url')->nullable();
            $table->string('board_url')->nullable();
            $table->string('git_url')->nullable();
            $table->enum('tipo_projeto', array_column(TipoProjeto::cases(), 'value'));
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('usuario_vinculo', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('projeto_id');
            $table->uuid('usuario_id');
            $table->enum('tipo_vinculo', array_column(TipoVinculo::cases(), 'value'));
            $table->enum('funcao', array_column(Funcao::cases(), 'value'));
            $table->enum('status', array_column(StatusVinculoProjeto::cases(), 'value'));
            $table->integer('carga_horaria_semanal');
            $table->dateTime('data_inicio');
            $table->dateTime('data_fim')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('projeto_id')->references('id')->on('projetos');
            $table->foreign('usuario_id')->references('id')->on('users');

            $table->index(['projeto_id', 'usuario_id', 'data_inicio']);
            $table->index(['usuario_id', 'status']);
        });

        // >>>>>>
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop tabelas em ordem reversa para respeitar constraints
        Schema::dropIfExists('usuario_vinculo');
        Schema::dropIfExists('projetos');
    }
};
