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
        Schema::create('daily_reports', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->date('data');
            $table->integer('horas_trabalhadas')->default(0)->comment('Horas trabalhadas no dia');
            $table->text('o_que_fez_ontem')->nullable()->comment('O que o colaborador fez no dia anterior');
            $table->text('o_que_vai_fazer_hoje')->nullable()->comment('O que o colaborador vai fazer hoje');
            $table->text('observacoes')->nullable()->comment('Observações adicionais');

            $table->timestamps();
            $table->softDeletes();

            $table->foreignUuid('usuario_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('projeto_id')->constrained('projetos')->onDelete('cascade');

            // Índices para performance
            $table->index(['usuario_id', 'data'], 'daily_reports_usuario_data_idx');
            $table->index(['projeto_id', 'data'], 'daily_reports_projeto_data_idx');
            $table->index(['data'], 'daily_reports_data_idx');

            // Única por usuário, data e projeto_id - um usuário pode ter apenas um daily report por dia
            $table->unique(['usuario_id', 'projeto_id', 'data'], 'daily_reports_usuario_data_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_reports');
    }
};
