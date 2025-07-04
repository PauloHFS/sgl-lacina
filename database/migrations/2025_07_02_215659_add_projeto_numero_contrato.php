<?php

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
        Schema::create('intervenientes_financeiros', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('nome')->unique();
            $table->timestamps();
        });

        Schema::table('projetos', function (Blueprint $table) {
            $table->string('numero_convenio')
                ->nullable()
                ->after('descricao')
                ->comment('Número do convênio ou contrato.');

            $table->foreignUuid('interveniente_financeiro_id')
                ->nullable()
                ->after('numero_convenio')
                ->comment('ID do interveniente financeiro responsável pelo projeto.')
                ->constrained('intervenientes_financeiros');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projetos', function (Blueprint $table) {
            $table->dropForeign(['interveniente_financeiro_id']);
            $table->dropColumn(['numero_convenio', 'interveniente_financeiro_id']);
        });

        Schema::dropIfExists('intervenientes_financeiros');
    }
};
