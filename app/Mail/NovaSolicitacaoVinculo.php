<?php

namespace App\Mail;

use App\Models\UsuarioProjeto;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NovaSolicitacaoVinculo extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public UsuarioProjeto $usuarioProjeto;

    public function __construct(UsuarioProjeto $usuarioProjeto)
    {
        $this->usuarioProjeto = $usuarioProjeto;
    }

    public function build()
    {
        return $this->subject('Nova solicitação de vínculo em projeto')
            ->markdown('emails.nova_solicitacao_vinculo', [
                'usuarioProjeto' => $this->usuarioProjeto,
            ]);
    }
}
