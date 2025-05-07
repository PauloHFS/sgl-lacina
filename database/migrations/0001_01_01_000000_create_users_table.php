<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Enums\StatusCadastro;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bancos', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->char('codigo', 3);
            $table->string('nome', 100)->unique();
        });

        // TODO Traduzir essa tabela
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();

            $table->enum('status_cadastro', array_column(StatusCadastro::cases(), 'value'))->default(StatusCadastro::PENDENTE->value);

            $table->string('linkedin_url')->nullable();
            $table->string('github_url')->nullable();
            $table->string('figma_url')->nullable();
            $table->text('curriculo')->nullable();
            $table->text('area_atuacao')->nullable();
            $table->text('tecnologias')->nullable();

            $table->char('cpf', 11)->nullable()->unique();
            $table->string('rg')->nullable()->unique();
            $table->char('uf_rg', 2)->nullable();
            $table->string('orgao_emissor_rg')->nullable();

            $table->string('telefone')->nullable();

            $table->uuid('banco_id')->nullable();
            $table->string('conta_bancaria')->nullable();
            $table->string('agencia')->nullable();

            $table->string('foto_url')->nullable();

            $table->string('genero')->nullable();
            $table->timestamp('data_nascimento')->nullable();

            $table->string('cep', 8)->nullable();
            $table->string('endereco')->nullable();
            $table->string('numero')->nullable();
            $table->string('complemento')->nullable();
            $table->string('bairro')->nullable();
            $table->string('cidade')->nullable();
            $table->string('uf', 2)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('banco_id')->references('id')->on('bancos');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignUuid('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tecnologias');
        Schema::dropIfExists('bancos');
        Schema::dropIfExists('areas_atuacao');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
        Schema::dropIfExists('status_cadastro');
    }
};
