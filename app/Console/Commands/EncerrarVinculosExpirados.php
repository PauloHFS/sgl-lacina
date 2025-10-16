<?php

namespace App\Console\Commands;

use App\Enums\StatusVinculoProjeto;
use App\Models\Projeto;
use App\Models\UsuarioProjeto;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EncerrarVinculosExpirados extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vinculos:encerrar-expirados {--dry-run : Simular a execução sem fazer alterações}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Encerra automaticamente os vínculos de usuários em projetos que chegaram à data de término';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('🔍 Executando em modo simulação (dry-run)...');
        }

        $this->info('🚀 Iniciando processo de encerramento de vínculos expirados...');

        try {
            // Buscar projetos que chegaram à data de término
            $projetosExpirados = Projeto::query()
                ->whereNotNull('data_termino')
                ->whereDate('data_termino', '<=', Carbon::now()->toDateString())
                ->with(['usuarios' => function ($query) {
                    $query->wherePivot('status', StatusVinculoProjeto::APROVADO->value)
                        ->wherePivotNull('data_fim')
                        ->withPivot('id', 'status', 'tipo_vinculo', 'data_fim');
                }])
                ->get();

            if ($projetosExpirados->isEmpty()) {
                $this->info('✅ Nenhum projeto expirado encontrado com vínculos ativos.');

                return 0;
            }

            $totalVinculosEncerrados = 0;
            $projetosAfetados = 0;

            if ($dryRun) {
                // Modo simulação - apenas exibir informações
                foreach ($projetosExpirados as $projeto) {
                    $vinculosAtivos = $projeto->usuarios;

                    if ($vinculosAtivos->isEmpty()) {
                        continue;
                    }

                    $projetosAfetados++;
                    $vinculosEncontrados = $vinculosAtivos->count();

                    $this->line("📁 Projeto: {$projeto->nome} (ID: {$projeto->id})");
                    $this->line("📅 Data de término: {$projeto->data_termino->format('d/m/Y')}");
                    $this->line("👥 Vínculos ativos encontrados: {$vinculosEncontrados}");

                    foreach ($vinculosAtivos as $usuario) {
                        $vinculo = $usuario->pivot;
                        $this->line("   • {$usuario->name} ({$usuario->email}) - {$vinculo->tipo_vinculo}");
                        $totalVinculosEncerrados++;
                    }

                    $this->line('');
                }
            } else {
                // Execução real com transação
                DB::transaction(function () use ($projetosExpirados, &$totalVinculosEncerrados, &$projetosAfetados) {
                    foreach ($projetosExpirados as $projeto) {
                        $vinculosAtivos = $projeto->usuarios;

                        if ($vinculosAtivos->isEmpty()) {
                            continue;
                        }

                        $projetosAfetados++;
                        $vinculosEncontrados = $vinculosAtivos->count();

                        $this->line("📁 Projeto: {$projeto->nome} (ID: {$projeto->id})");
                        $this->line("📅 Data de término: {$projeto->data_termino->format('d/m/Y')}");
                        $this->line("👥 Vínculos ativos encontrados: {$vinculosEncontrados}");

                        foreach ($vinculosAtivos as $usuario) {
                            $vinculo = $usuario->pivot;

                            $this->line("   • {$usuario->name} ({$usuario->email}) - {$vinculo->tipo_vinculo}");

                            // Atualizar o vínculo na tabela usuario_projeto
                            UsuarioProjeto::where('id', $vinculo->id)
                                ->update([
                                    'status' => StatusVinculoProjeto::ENCERRADO,
                                    'data_fim' => $projeto->data_termino,
                                    'updated_at' => Carbon::now(),
                                ]);

                            $totalVinculosEncerrados++;
                        }

                        $this->line('');
                    }
                });
            }
        } catch (\Exception $e) {
            if ($dryRun && $e->getMessage() === 'Dry run - reverting transaction') {
                // Esperado em modo dry-run
                $this->newLine();
                $this->info('📊 Resumo da simulação:');
                $this->info("   • Projetos que seriam afetados: {$projetosAfetados}");
                $this->info("   • Vínculos que seriam encerrados: {$totalVinculosEncerrados}");
                $this->newLine();
                $this->warn('⚠️  Nenhuma alteração foi feita (modo simulação).');
                $this->info('💡 Para executar as alterações, rode o comando sem a flag --dry-run');

                return 0;
            }

            Log::error('Erro ao encerrar vínculos expirados: '.$e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);

            $this->error("❌ Erro durante a execução: {$e->getMessage()}");

            return 1;
        }

        // Log da operação realizada
        Log::info('Vínculos expirados encerrados automaticamente', [
            'projetos_afetados' => $projetosAfetados,
            'vinculos_encerrados' => $totalVinculosEncerrados,
            'executado_em' => Carbon::now()->toDateTimeString(),
        ]);

        $this->newLine();
        $this->info('✅ Processo concluído com sucesso!');
        $this->info('📊 Resumo:');
        $this->info("   • Projetos afetados: {$projetosAfetados}");
        $this->info("   • Vínculos encerrados: {$totalVinculosEncerrados}");

        return 0;
    }
}
