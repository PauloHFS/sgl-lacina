<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class HorariosCacheService
{
    private const CACHE_TTL = 86400; // 24 horas
    private const CACHE_PREFIX = 'horarios_usuario_';

    /**
     * Obter horas trabalhadas por dia da semana para um usuário
     */
    public function getHorasPorDiaDaSemana(User $user): array
    {
        $cacheKey = self::CACHE_PREFIX . $user->id;

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user) {
            return $this->calcularHorasPorDiaDaSemana($user);
        });
    }

    /**
     * Obter horas trabalhadas por dia da semana para um usuário em um projeto específico
     */
    public function getHorasPorDiaDaSemanaProjetoPorProjeto(User $user): array
    {
        $cacheKey = self::CACHE_PREFIX . 'projetos_' . $user->id;

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user) {
            return $this->calcularHorasPorProjetoEDia($user);
        });
    }

    /**
     * Invalidar cache de um usuário específico
     */
    public function invalidarCacheUsuario(User $user): void
    {
        $cacheKey = self::CACHE_PREFIX . $user->id;
        $cacheKeyProjetos = self::CACHE_PREFIX . 'projetos_' . $user->id;

        Cache::forget($cacheKey);
        Cache::forget($cacheKeyProjetos);
    }

    /**
     * Calcular horas trabalhadas por dia da semana
     */
    private function calcularHorasPorDiaDaSemana(User $user): array
    {
        $diasDaSemana = [
            'SEGUNDA' => 0,
            'TERCA' => 0,
            'QUARTA' => 0,
            'QUINTA' => 0,
            'SEXTA' => 0,
            'SABADO' => 0,
            'DOMINGO' => 0,
        ];

        // Buscar todos os horários de trabalho do usuário agrupados por dia
        $horarios = $user->horarios()
            ->whereIn('tipo', ['TRABALHO_PRESENCIAL', 'TRABALHO_REMOTO'])
            ->selectRaw('dia_da_semana, COUNT(*) as total_horas')
            ->groupBy('dia_da_semana')
            ->get();

        foreach ($horarios as $horario) {
            $diasDaSemana[$horario->dia_da_semana->value] = (int) $horario->total_horas;
        }

        Log::info("Horas calculadas para usuário {$user->id}", $diasDaSemana);

        return $diasDaSemana;
    }

    /**
     * Obter horas para uma data específica
     */
    public function getHorasParaData(User $user, string $data): int
    {
        $dataObj = \Carbon\Carbon::parse($data);
        $diaDaSemana = $this->mapearDiaDaSemanaPtBr($dataObj->format('l'));

        $horasPorDia = $this->getHorasPorDiaDaSemana($user);

        return $horasPorDia[$diaDaSemana] ?? 0;
    }

    /**
     * Mapear dias da semana em inglês para português
     */
    private function mapearDiaDaSemanaPtBr(string $diaDaSemanaIngles): string
    {
        $mapeamento = [
            'Monday' => 'SEGUNDA',
            'Tuesday' => 'TERCA',
            'Wednesday' => 'QUARTA',
            'Thursday' => 'QUINTA',
            'Friday' => 'SEXTA',
            'Saturday' => 'SABADO',
            'Sunday' => 'DOMINGO',
        ];

        return $mapeamento[$diaDaSemanaIngles] ?? 'SEGUNDA';
    }

    /**
     * Invalidar cache de todos os usuários (usar com cuidado)
     */
    public function invalidarTodoCache(): void
    {
        // Buscar todas as chaves que começam com o prefixo
        $pattern = self::CACHE_PREFIX . '*';

        if (Cache::getStore() instanceof \Illuminate\Cache\RedisStore) {
            // Para Redis
            $keys = Cache::getRedis()->keys($pattern);
            if (!empty($keys)) {
                Cache::getRedis()->del($keys);
            }
        } else {
            // Para outros drivers, invalidar manualmente (menos eficiente)
            Log::warning("Cache invalidation não otimizada para driver " . config('cache.default'));
        }

        Log::info("Cache de horários invalidado para todos os usuários");
    }

    /**
     * Calcular horas trabalhadas por projeto e dia da semana
     */
    private function calcularHorasPorProjetoEDia(User $user): array
    {
        $resultado = [];

        // Buscar todos os projetos ativos do usuário
        $projetosUsuario = $user->vinculos()
            ->with('projeto')
            ->where('status', 'APROVADO')
            ->whereNull('data_fim')
            ->get();

        foreach ($projetosUsuario as $usuarioProjeto) {
            $projetoId = $usuarioProjeto->projeto_id;

            $diasDaSemana = [
                'SEGUNDA' => 0,
                'TERCA' => 0,
                'QUARTA' => 0,
                'QUINTA' => 0,
                'SEXTA' => 0,
                'SABADO' => 0,
                'DOMINGO' => 0,
            ];

            // Buscar horários específicos deste projeto
            $horarios = $user->horarios()
                ->where('usuario_projeto_id', $usuarioProjeto->id)
                ->whereIn('tipo', ['TRABALHO_PRESENCIAL', 'TRABALHO_REMOTO'])
                ->selectRaw('dia_da_semana, COUNT(*) as total_horas')
                ->groupBy('dia_da_semana')
                ->get();

            foreach ($horarios as $horario) {
                $diasDaSemana[$horario->dia_da_semana->value] = (int) $horario->total_horas;
            }

            $resultado[$projetoId] = $diasDaSemana;
        }

        Log::info("Horas por projeto calculadas para usuário {$user->id}", $resultado);

        return $resultado;
    }
}
