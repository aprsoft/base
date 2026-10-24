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

    /**
     * Inicializa los servicios y reglas globales.
     *
     * Configura una autorización previa mediante Gate::before():
     *
     * - Deniega acciones sobre la cuenta principal protegida.
     * - SuperAdmin conserva acceso global al resto del sistema.
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
        Gate::policy(User::class, UserPolicy::class);

        Gate::before(function (User $user, string $ability, array $arguments = []): ?bool {
            // La cuenta inicial queda fuera de las autorizaciones por modelo.
            $target = $arguments[0] ?? null;

            if ($target instanceof User && $target->isPrimarySuperAdmin()) {
                return false;
            }

            return $user->hasRole('SuperAdmin', 'web') ? true : null;
        });
    }
}
