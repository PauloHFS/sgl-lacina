<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Enums\StatusAusencia;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ausencias', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('usuario_id');
            $table->uuid('projeto_id');
            $table->text('titulo');
            $table->date('data_inicio')->comment('Data de início inclusiva da ausencia');
            $table->date('data_fim')->comment('Data de fim inclusiva da ausencia');
            $table->text('justificativa');
            $table->enum('status', array_column(StatusAusencia::cases(), 'value'))->default(StatusAusencia::PENDENTE->value);

            // plano de compensacao
            $table->integer('horas_a_compensar')->default(0);
            $table->date('compensacao_data_inicio')->comment('Data de início inclusiva da ausencia');
            $table->date('compensacao_data_fim')->comment('Data de fim inclusiva da ausencia');
            $table->jsonb('compensacao_horarios')->comment('Horários de compensação em formato JSON {data, horario [0-23]}');

            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('projeto_id')->references('id')->on('projetos')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();

            $table->index('usuario_id');
            $table->index('projeto_id');
            $table->index(['data_inicio', 'data_fim']);

            $table->unique(['usuario_id', 'projeto_id', 'data_inicio'], 'unique_ausencia_por_data_inicio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ausencias');
    }
};
