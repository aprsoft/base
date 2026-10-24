<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\ProfilePolicy;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use App\Support\Security\ProtectedSystemAccount;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);

        /*
         * Autorizaciones del perfil propio.
         */
        Gate::define('profile.view', [
            ProfilePolicy::class,
            'view',
        ]);

        Gate::define('profile.update', [
            ProfilePolicy::class,
            'update',
        ]);

        Gate::define('profile.delete', [
            ProfilePolicy::class,
            'delete',
        ]);

        Gate::define('profile.changePassword', [
            ProfilePolicy::class,
            'changePassword',
        ]);

        /*
         * Acceso global de SuperAdmin con excepciones.
         */
        Gate::before(function (
            User $actor,
            string $ability,
            array $arguments = []
        ): ?bool {
            $target = $arguments[0] ?? null;

            /*
             * El rol reservado SuperAdmin no puede
             * modificarse ni eliminarse.
             */
            if (
                $target instanceof Role
                && $target->name === 'SuperAdmin'
                && $target->guard_name === 'web'
                && in_array(
                    $ability,
                    ['update', 'delete', 'forceDelete'],
                    true
                )
            ) {
                return false;
            }

            /*
             * La cuenta principal está protegida
             * de toda modificación administrativa.
             */
            if (
                $target instanceof User
                && ProtectedSystemAccount::isPrimary($target)
            ) {
                // Única excepción: su propia contraseña.
                if ($ability === 'profile.changePassword') {
                    return $actor->is($target);
                }

                return false;
            }

            /*
             * Todas las acciones de perfil propio
             * deben pasar por ProfilePolicy.
             *
             * Esto incluye a TODOS los SuperAdmin.
             */
            if (str_starts_with($ability, 'profile.')) {
                return null;
            }

            /*
             * Las acciones administrativas sobre User
             * deben pasar obligatoriamente por UserPolicy.
             *
             * Así ningún SuperAdmin puede saltarse
             * la jerarquía acordada.
             */
            if (
                $target instanceof User
                && in_array(
                    $ability,
                    [
                        'update',
                        'delete',
                        'suspend',
                        'restore',
                        'forceDelete',
                    ],
                    true
                )
            ) {
                return null;
            }

            /*
             * Acceso global restante.
             */
            return $actor->hasRole('SuperAdmin', 'web')
                ? true
                : null;
        });
    }
}
