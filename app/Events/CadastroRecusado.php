<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CadastroRecusado
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public array $dadosColaborador, public ?string $url = null, public ?string $observacao = null)
    {
        $this->dadosColaborador = $dadosColaborador;
        $this->url = $url ?? config('app.url') . '/register';
        $this->observacao = $observacao;
    }
}
