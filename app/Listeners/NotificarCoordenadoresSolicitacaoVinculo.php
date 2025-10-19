<?php

namespace App\Listeners;

use App\Enums\StatusVinculoProjeto;
use App\Enums\TipoVinculo;
use App\Events\SolicitacaoVinculoCriada;
use App\Mail\NovaSolicitacaoVinculo;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class NotificarCoordenadoresSolicitacaoVinculo implements ShouldQueue
{
    public function handle(SolicitacaoVinculoCriada $event): void
    {
        $projetoId = $event->usuarioProjeto->projeto_id;

        $coordenadores = User::whereHas('vinculos', function ($q) use ($projetoId) {
            $q->where('projeto_id', $projetoId)
                ->where('tipo_vinculo', TipoVinculo::COORDENADOR)
                ->where('status', StatusVinculoProjeto::APROVADO);
        })
            ->distinct('email')
            ->get();

        foreach ($coordenadores as $coordenador) {
            Mail::to($coordenador->email)->queue(
                new NovaSolicitacaoVinculo($event->usuarioProjeto)
            );
        }
    }
}
