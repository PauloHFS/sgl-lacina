<?php

namespace App\Providers;

use App\Models\Ausencia;
use App\Policies\AusenciaPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Ausencia::class => AusenciaPolicy::class,
        \App\Models\Projeto::class => \App\Policies\ProjetoPolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();
    }
}
