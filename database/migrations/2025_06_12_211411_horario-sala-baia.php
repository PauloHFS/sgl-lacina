<?php

use App\Enums\DiaDaSemana;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Enums\TipoHorario;

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
            $table->integer('horario');
            $table->enum('dia_da_semana', array_column(DiaDaSemana::cases(), 'value'));
            $table->enum('tipo', array_column(TipoHorario::cases(), 'value'))->default(TipoHorario::AUSENTE->value);

            $table->timestamps();
            $table->softDeletes();

            $table->foreignUuid('usuario_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('usuario_projeto_id')->nullable()->constrained('usuario_projeto')->onDelete('set null');
            $table->foreignUuid('baia_id')->nullable()->constrained('baias')->onDelete('set null');

            $table->index(['usuario_id', 'dia_da_semana', 'horario']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horarios');
        Schema::dropIfExists('baias');
        Schema::dropIfExists('salas');
    }
};
