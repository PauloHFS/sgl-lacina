<?php

namespace App\Events;

use App\Models\Projeto;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VinculoAceito
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User $user;

    public Projeto $projeto;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user, Projeto $projeto)
    {
        $this->user = $user;
        $this->projeto = $projeto;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
