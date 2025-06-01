<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CadastroRecusado
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $dadosColaborador;

    /**
     * Create a new event instance.
     */
    public function __construct(array $dadosColaborador)
    {
        $this->dadosColaborador = $dadosColaborador;
    }
}
