<?php

namespace App\Events;

use App\Models\User; // Corrected: Single backslash
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CadastroAceito
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public User $user, public $url = null, public $observacao = null)
    {
        $this->user = $user;
        $this->url = $url ?? config('app.url') . '/dashboard';
        $this->observacao = $observacao;
    }
}
