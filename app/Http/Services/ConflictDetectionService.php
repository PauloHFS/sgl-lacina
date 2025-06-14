<?php

namespace App\Http\Services;

use App\Models\Horario;
use App\Models\Projeto;
use App\Models\User;
use App\Enums\TipoHorario;
use Illuminate\Support\Collection;

class ConflictDetectionService
{
    public function detectarConflitos(User $usuario, ?Projeto $projeto = null): Collection
    {
        $query = Horario::with(['usuarioProjeto.usuario', 'usuarioProjeto.projeto'])
            ->whereHas('usuarioProjeto', function ($q) use ($usuario) {
                $q->where('usuario_id', $usuario->id);
            });

        if ($projeto) {
            $query->porProjeto($projeto->id);
        }

        $horarios = $query->get();
        $conflitos = collect();

        foreach ($horarios as $horario) {
            $conflitantes = Horario::conflitoCom($horario)->get();

            if ($conflitantes->isNotEmpty()) {
                $conflitos->push([
                    'horario_principal' => $horario,
                    'conflitantes' => $conflitantes,
                    'projeto' => $horario->projeto,
                    'usuario' => $horario->usuario,
                    'tipo_conflito' => $this->determinarTipoConflito($horario, $conflitantes),
                ]);
            }
        }

        return $conflitos;
    }

    public function detectarConflitosGlobais(Projeto $projeto): Collection
    {
        $horarios = Horario::porProjeto($projeto->id)
            ->with(['usuarioProjeto.usuario'])
            ->get();

        $conflitos = collect();

        foreach ($horarios as $horario) {
            // Detecta conflitos de baia (duas pessoas na mesma baia ao mesmo tempo)
            if ($horario->tipo === TipoHorario::TRABALHO_PRESENCIAL) {
                $conflitosPresenciais = $this->detectarConflitosPresenciais($horario);
                if ($conflitosPresenciais->isNotEmpty()) {
                    $conflitos = $conflitos->merge($conflitosPresenciais);
                }
            }
        }

        return $conflitos;
    }

    private function detectarConflitosPresenciais(Horario $horario): Collection
    {
        $conflitos = collect();

        // Verifica conflitos de baia
        foreach ($horario->baias as $baia) {
            $horariosMesmaBaia = Horario::whereHas('baias', function ($q) use ($baia) {
                $q->where('baia_id', $baia->id);
            })
                ->where('id', '!=', $horario->id)
                ->where('dia_semana', $horario->dia_semana)
                ->where('tipo', TipoHorario::TRABALHO_PRESENCIAL)
                ->where(function ($q) use ($horario) {
                    $q->whereBetween('hora_inicio', [$horario->hora_inicio, $horario->hora_fim])
                        ->orWhereBetween('hora_fim', [$horario->hora_inicio, $horario->hora_fim]);
                })
                ->get();

            if ($horariosMesmaBaia->isNotEmpty()) {
                $conflitos->push([
                    'tipo' => 'conflito_baia',
                    'baia' => $baia,
                    'horario_principal' => $horario,
                    'conflitantes' => $horariosMesmaBaia,
                    'mensagem' => "Conflito de ocupação da baia {$baia->numero}",
                ]);
            }
        }

        return $conflitos;
    }

    private function determinarTipoConflito(Horario $principal, Collection $conflitantes): string
    {
        $tiposPrincipais = [$principal->tipo->value];
        $tiposConflitantes = $conflitantes->pluck('tipo')->map(fn($t) => $t->value)->toArray();

        if (in_array('EM_AULA', $tiposPrincipais) || in_array('EM_AULA', $tiposConflitantes)) {
            return 'conflito_aula_trabalho';
        }

        if (in_array('NA_BAIA', $tiposPrincipais) && in_array('NA_BAIA', $tiposConflitantes)) {
            return 'conflito_presencial';
        }

        if (in_array('AUSENTE', $tiposPrincipais) || in_array('AUSENTE', $tiposConflitantes)) {
            return 'conflito_disponibilidade';
        }

        return 'conflito_geral';
    }

    public function gerarRelatorioOcupacao(Projeto $projeto, array $periodo): array
    {
        $horarios = Horario::porProjeto($projeto->id)
            ->disponivel() // Apenas TRABALHO_PRESENCIAL e TRABALHO_REMOTO
            ->get();

        $estatisticas = [
            'total_horas' => $horarios->sum(function ($h) {
                return $h->hora_fim->diffInHours($h->hora_inicio);
            }),
            'horas_presenciais' => $horarios->where('tipo', TipoHorario::TRABALHO_PRESENCIAL)->sum(function ($h) {
                return $h->hora_fim->diffInHours($h->hora_inicio);
            }),
            'horas_remotas' => $horarios->where('tipo', TipoHorario::TRABALHO_REMOTO)->sum(function ($h) {
                return $h->hora_fim->diffInHours($h->hora_inicio);
            }),
            'taxa_presencial' => 0,
            'baias_utilizadas' => $horarios->where('tipo', TipoHorario::TRABALHO_PRESENCIAL)
                ->flatMap(fn($h) => $h->baias)
                ->unique('id')
                ->count(),
        ];

        if ($estatisticas['total_horas'] > 0) {
            $estatisticas['taxa_presencial'] = round(
                ($estatisticas['horas_presenciais'] / $estatisticas['total_horas']) * 100,
                2
            );
        }

        return $estatisticas;
    }
}
