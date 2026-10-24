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

        // ProfilePolicy se registra mediante habilidades específicas,
        // porque User::class ya utiliza UserPolicy.
        Gate::define('profile.view', [
            ProfilePolicy::class, 'view',
        ]);

        Gate::define('profile.update', [
            ProfilePolicy::class, 'update',
        ]);

        Gate::define('profile.delete', [
            ProfilePolicy::class, 'delete',
        ]);

        Gate::define('profile.changePassword', [
            ProfilePolicy::class, 'changePassword',
        ]);

        Gate::before(function (
            User $actor,
            string $ability,
            array $arguments = []
        ): ?bool {
            $target = $arguments[0] ?? null;

            // Protección absoluta del rol reservado en Gate.
            if (
                $target instanceof Role
                && $target->name === 'SuperAdmin'
                && $target->guard_name === 'web'
            ) {
                if (in_array($ability, [
                    'update',
                    'delete',
                    'forceDelete',
                ], true)) {
                    return false;
                }
            }

            // La cuenta principal no admite operaciones sobre su
            // perfil ni acciones administrativas dirigidas a ella.
            if (
                $target instanceof User
                && ProtectedSystemAccount::isPrimary($target)
            ) {
                if ($ability === 'profile.changePassword') {
                    return $actor->is($target);
                }

                return false;
            }

            // Las reglas de perfiles pasan por ProfilePolicy.
            if (str_starts_with($ability, 'profile.')) {
                return null;
            }

            // Acceso general de SuperAdmin.
            return $actor->hasRole('SuperAdmin', 'web')
                ? true
                : null;
        });
    }
}
