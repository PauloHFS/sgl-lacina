<?php

namespace App\Listeners;

use App\Events\SolicitacaoVinculoCriada;
use App\Enums\TipoVinculo;
use App\Enums\StatusVinculoProjeto;
use App\Models\User;
use App\Mail\NovaSolicitacaoVinculo;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;

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
