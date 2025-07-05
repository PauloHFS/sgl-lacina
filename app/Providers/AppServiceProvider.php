<?php

namespace App\Providers;

use App\Models\Baia;
use App\Models\UsuarioProjeto;
use App\Observers\BaiaObserver;
use App\Observers\UsuarioProjetoObserver;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
        UsuarioProjeto::observe(UsuarioProjetoObserver::class);
        Baia::observe(BaiaObserver::class);
    }
}
