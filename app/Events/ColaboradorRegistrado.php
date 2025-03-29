<?php

namespace App\Events;

use Illuminate\Auth\Events\Registered;

class ColaboradorRegistrado extends Registered
{

    public $docente_email;

    /**
     * Create a new event instance.
     */
    public function __construct($user, $docente_email)
    {
        parent::__construct($user);
        $this->docente_email = $docente_email;
    }
}
