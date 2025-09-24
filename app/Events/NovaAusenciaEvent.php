<?php

namespace App\Events;

use App\Models\Ausencia;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NovaAusenciaEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Ausencia $ausencia;

    /**
     * Create a new event instance.
     */
    public function __construct(Ausencia $ausencia)
    {
        $this->ausencia = $ausencia;
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
