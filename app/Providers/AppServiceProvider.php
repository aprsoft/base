<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/**
 * Proveedor principal de servicios de la aplicación.
 *
 * Permite registrar servicios e inicializar configuraciones
 * globales de Laravel.
 *
 * En este proyecto también establece una regla de autorización
 * global para los usuarios con el rol SuperAdmin, utilizando
 * Laravel Gate y Spatie Laravel Permission.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Registra servicios en el contenedor de Laravel.
     *
     * Actualmente no se registran servicios adicionales.
     */
    public function register(): void
    {
        //
    }

   
    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);

        Gate::before(function ($user, $ability) {
            return $user->hasRole('SuperAdmin')
                ? true
                : null;
        });
    }
}
