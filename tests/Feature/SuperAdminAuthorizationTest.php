<?php

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;

test('la jerarquia SuperAdmin respeta las autorizaciones', function () {
    $role = Role::firstOrCreate([
        'name' => 'SuperAdmin',
        'guard_name' => 'web',
    ]);

    // Cuenta principal.
    $principal = User::factory()->create();
    $principal->forceFill([
        'system_account_key' => User::PRIMARY_SUPER_ADMIN_KEY,
    ])->save();
    $principal->assignRole($role);

    // Dos SuperAdmin secundarios.
    $secundarioA = User::factory()->create();
    $secundarioA->assignRole($role);

    $secundarioB = User::factory()->create();
    $secundarioB->assignRole($role);

    // El principal puede editar a los secundarios.
    $this->assertTrue(
        Gate::forUser($principal)->allows('update', $secundarioA)
    );

    // Un secundario no puede editar a otro secundario.
    $this->assertFalse(
        Gate::forUser($secundarioA)->allows('update', $secundarioB)
    );

    // Nadie puede editar al principal.
    $this->assertFalse(
        Gate::forUser($secundarioA)->allows('update', $principal)
    );

    // Ningún SuperAdmin puede editarse a sí mismo.
    $this->assertFalse(
        Gate::forUser($principal)->allows('update', $principal)
    );

    $this->assertFalse(
        Gate::forUser($secundarioA)->allows('update', $secundarioA)
    );

    // Ambos pueden administrar otro secundario.
    foreach (['suspend', 'restore', 'delete'] as $ability) {
        $this->assertTrue(
            Gate::forUser($principal)->allows(
                $ability,
                $secundarioB
            )
        );

        $this->assertTrue(
            Gate::forUser($secundarioA)->allows(
                $ability,
                $secundarioB
            )
        );

        // Protección del principal.
        $this->assertFalse(
            Gate::forUser($secundarioA)->allows(
                $ability,
                $principal
            )
        );

        // Prohibida la autoeliminación/autosuspensión.
        $this->assertFalse(
            Gate::forUser($secundarioA)->allows(
                $ability,
                $secundarioA
            )
        );
    }

    // Ambos pueden cambiar únicamente
    // su propia contraseña.
    foreach ([$principal, $secundarioA] as $actor) {
        $gate = Gate::forUser($actor);

        $this->assertTrue(
            $gate->allows('profile.changePassword', $actor)
        );

        $this->assertFalse(
            $gate->allows('profile.view', $actor)
        );

        $this->assertFalse(
            $gate->allows('profile.update', $actor)
        );

        $this->assertFalse(
            $gate->allows('profile.delete', $actor)
        );
    }
});
