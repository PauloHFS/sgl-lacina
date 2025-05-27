<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CadastroAceito
{
    public $user_id;
    public $user_email;

    /**
     * Create a new event instance.
     */
    public function __construct($user_id, $user_email)
    {
        $this->user_id = $user_id;
        $this->user_email = $user_email;
    }
}
