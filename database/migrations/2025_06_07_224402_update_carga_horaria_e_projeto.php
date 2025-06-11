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
        Schema::table('projetos', function (Blueprint $table) {
            $table->integer('valor_total')->default(0)->after('descricao');
            $table->float("meses_execucao")->default(0)->after('valor_total');
            $table->jsonb("campos_extras")->default(DB::raw("'{}'::jsonb"))->after('meses_execucao');
        });

        Schema::table('usuario_projeto', function (Blueprint $table) {
            $table->renameColumn('carga_horaria_semanal', 'carga_horaria');
        });

        // Também renomear na tabela de histórico para manter consistência
        Schema::table('historico_usuario_projeto', function (Blueprint $table) {
            $table->renameColumn('carga_horaria_semanal', 'carga_horaria');
        });

        // Multiplicar carga_horaria por 4 (convertendo de semanal para mensal)
        DB::table('usuario_projeto')->update([
            'carga_horaria' => DB::raw('carga_horaria * 4')
        ]);

        DB::table('historico_usuario_projeto')->update([
            'carga_horaria' => DB::raw('carga_horaria * 4')
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projetos', function (Blueprint $table) {
            $table->dropColumn('valor_total');
            $table->dropColumn('meses_execucao');
            $table->dropColumn('campos_extras');
        });

        // Dividir carga_horaria por 4 (convertendo de mensal para semanal)
        // Primeiro dividir os valores antes de renomear as colunas
        DB::table('usuario_projeto')->update([
            'carga_horaria' => DB::raw('carga_horaria / 4')
        ]);

        DB::table('historico_usuario_projeto')->update([
            'carga_horaria' => DB::raw('carga_horaria / 4')
        ]);

        Schema::table('usuario_projeto', function (Blueprint $table) {
            $table->renameColumn('carga_horaria', 'carga_horaria_semanal');
        });

        Schema::table('historico_usuario_projeto', function (Blueprint $table) {
            $table->renameColumn('carga_horaria', 'carga_horaria_semanal');
        });
    }
};
