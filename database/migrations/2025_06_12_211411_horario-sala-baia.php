<?php

use App\Enums\DiaDaSemana;
use App\Enums\TipoHorario;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('salas', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('nome')->unique();
            $table->text('descricao')->nullable();
            $table->boolean('ativa')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['ativa', 'created_at']);
        });

        Schema::create('baias', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('nome')->unique();
            $table->text('descricao')->nullable();
            $table->boolean('ativa')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->foreignUuid('sala_id')->constrained('salas')->onDelete('cascade');

            $table->index(['ativa', 'created_at']);
        });

        Schema::create('horarios', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->integer('horario')->check('horario >= 0 AND horario <= 23');
            $table->enum('dia_da_semana', array_column(DiaDaSemana::cases(), 'value'));
            $table->enum('tipo', array_column(TipoHorario::cases(), 'value'))->default(TipoHorario::AUSENTE->value);

            $table->timestamps();
            $table->softDeletes();

            $table->foreignUuid('usuario_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('usuario_projeto_id')->nullable()->constrained('usuario_projeto')->onDelete('set null');
            $table->foreignUuid('baia_id')->nullable()->constrained('baias')->onDelete('set null');

            // Índice único composto - evita duplicatas e otimiza consultas por usuário
            $table->unique(['usuario_id', 'dia_da_semana', 'horario'], 'horarios_usuario_dia_horario_unique');

            // Índice para relatórios por dia da semana
            $table->index(['dia_da_semana', 'horario', 'tipo'], 'horarios_dia_horario_tipo_idx');

            // Índice para consultas por projeto
            $table->index(['usuario_projeto_id', 'dia_da_semana'], 'horarios_projeto_dia_idx')
                ->where('usuario_projeto_id', 'IS NOT NULL');

            // Índice para horários de trabalho (presencial/remoto)
            $table->index(['tipo', 'dia_da_semana', 'horario'], 'horarios_trabalho_idx')
                ->whereIn('tipo', [TipoHorario::TRABALHO_PRESENCIAL->value, TipoHorario::TRABALHO_REMOTO->value]);
        });

        DB::statement('
            CREATE UNIQUE INDEX horarios_baia_dia_horario_unique
            ON horarios (baia_id, dia_da_semana, horario)
            WHERE baia_id IS NOT NULL AND deleted_at IS NULL
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS horarios_baia_dia_horario_unique');
        Schema::dropIfExists('horarios');
        Schema::dropIfExists('baias');
        Schema::dropIfExists('salas');
    }
};
