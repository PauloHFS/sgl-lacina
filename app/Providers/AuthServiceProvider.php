<?php

namespace App\Providers;

use App\Models\Ausencia;
use App\Models\User;
use App\Policies\AusenciaPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Ausencia::class => AusenciaPolicy::class,
        \App\Models\Projeto::class => \App\Policies\ProjetoPolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();

        Gate::before(function (User $user, string $ability) {
            if ($user->isCoordenadorMaster()) {
                return true;
            }

            return null;
        });
    }
}
