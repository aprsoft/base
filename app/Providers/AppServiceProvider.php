<?php

namespace App\Providers;

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

    /**
     * Inicializa los servicios y reglas globales.
     *
     * Configura una autorización previa mediante Gate::before():
     *
     * - Si el usuario tiene el rol SuperAdmin, concede
     *   la autorización sin evaluar las reglas posteriores.
     *
     * - Si el usuario no tiene el rol SuperAdmin, devuelve
     *   null para continuar con la evaluación normal
     *   de permisos, Gates y Policies.
     *
     * Esta regla afecta las autorizaciones realizadas
     * mediante Laravel Gate, como can() y @can.
     *
     * No sustituye la autenticación ni las restricciones
     * de seguridad que no utilizan Laravel Gate.
     */
    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            return $user->hasRole('SuperAdmin')
                ? true
                : null;
        });
    }
}
