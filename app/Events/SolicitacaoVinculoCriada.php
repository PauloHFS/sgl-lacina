<?php

namespace App\Events;

use App\Models\UsuarioProjeto;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SolicitacaoVinculoCriada
{
    use Dispatchable, SerializesModels;

    public UsuarioProjeto $usuarioProjeto;

    public function __construct(UsuarioProjeto $usuarioProjeto)
    {
        $this->usuarioProjeto = $usuarioProjeto;
    }
}
