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
        Schema::create('dailies', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('usuario_id');
            $table->uuid('usuario_projeto_id')->nullable(); // Vínculo opcional com projeto
            $table->date('data'); // Data do daily
            $table->text('ontem')->nullable(); // O que foi realizado/alcançado ontem
            $table->text('observacoes')->nullable(); // Dificuldades ou observações sobre o último dia
            $table->text('hoje'); // O que será realizado/concluído hoje, sempre uma meta concreta
            $table->integer('carga_horaria'); // Horas de trabalho planejadas para hoje (1-9)

            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('usuario_projeto_id')->references('id')->on('usuario_projeto')->onDelete('set null');

            // Índices para otimização
            $table->index(['usuario_id', 'data']);
            $table->index(['usuario_projeto_id', 'data']);
            $table->index('data');

            // Constraint para garantir um daily por usuário por dia
            $table->unique(['usuario_id', 'data'], 'dailies_usuario_data_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dailies');
    }
};
