<?php

namespace App\Providers;

use App\Models\ErrorLog;
use App\Models\User;
use App\Policies\ErrorLogPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\ProfilePolicy;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;
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
        Gate::policy(Permission::class, PermissionPolicy::class);
        Gate::policy(ErrorLog::class, ErrorLogPolicy::class);

        // El perfil propio se autoriza mediante Gates con nombre.
        Gate::define('profile.view', [ProfilePolicy::class, 'view']);
        Gate::define('profile.update', [ProfilePolicy::class, 'update']);
        // Gate::define('profile.delete', [ProfilePolicy::class, 'delete']);
        Gate::define('profile.changePassword', [ProfilePolicy::class, 'changePassword']);

        Gate::before(function (User $actor, string $ability, array $arguments = []): ?bool {
            $subject = $arguments[0] ?? null;

            // Estas acciones tienen restricciones que ni SuperAdmin puede omitir.
            if (str_starts_with($ability, 'profile.')) {
                return null;
            }

            $protectedModels = [
                User::class,
                Role::class,
                Permission::class,
                ErrorLog::class,
            ];

            foreach ($protectedModels as $model) {
                if ($subject instanceof $model || $subject === $model) {
                    return null;
                }
            }

            // Acceso global para el resto de capacidades.
            return $actor->hasRole('SuperAdmin', 'web') ? true : null;
        });
    }
}
