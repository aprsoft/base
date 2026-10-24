<?php

use App\Livewire\User\UserCreate;
use App\Livewire\User\UserTable;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

test('el seeder es repetible y conserva las credenciales existentes', function () {
    $this->seed(DatabaseSeeder::class);
    $principal = User::where('system_account_key', User::PRIMARY_SUPER_ADMIN_KEY)->firstOrFail();
    $password = $principal->password;
    $users = User::count();
    expect(Hash::check('password', $password))->toBeTrue();
    $this->seed(DatabaseSeeder::class);
    expect(User::count())->toBe($users)
        ->and($principal->fresh()->password)->toBe($password)
        ->and(User::where('system_account_key', User::PRIMARY_SUPER_ADMIN_KEY)->count())->toBe(1);
});

test('el seeder no adopta un primer ID arbitrario', function () {
    User::factory()->create(['email' => 'otro@example.org']);
    $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    $this->expectException(\RuntimeException::class);
    $this->seed(\Database\Seeders\UserSeeder::class);
});

test('la cuenta principal solo puede cambiar su contraseña', function () {
    $this->seed(DatabaseSeeder::class);
    $principal = User::where('system_account_key', User::PRIMARY_SUPER_ADMIN_KEY)->firstOrFail();
    $this->actingAs($principal)->get(route('settings.profile.edit'))->assertForbidden();
    $this->actingAs($principal)->put(route('settings.profile.update'), [
        'name' => 'Alterado', 'email' => 'alterado@example.org',
    ])->assertForbidden();
    $this->actingAs($principal)->delete(route('settings.profile.destroy'))->assertForbidden();
    $this->actingAs($principal)->get(route('settings.password.edit'))->assertOk();
    $this->actingAs($principal)->put(route('settings.password.update'), [
        'current_password' => 'password',
        'password' => 'nueva-clave-desarrollo',
        'password_confirmation' => 'nueva-clave-desarrollo',
    ])->assertSessionHasNoErrors();

    expect($principal->fresh()->name)->toBe('SuperAdministrador')
        ->and($principal->fresh()->email)->toBe('superadmin@aprsoft.cl')
        ->and($principal->fresh()->trashed())->toBeFalse()
        ->and(Hash::check('nueva-clave-desarrollo', $principal->fresh()->password))->toBeTrue();
});

test('la cuenta principal no es visible ni editable para ningún SuperAdmin', function () {
    $this->seed(DatabaseSeeder::class);
    $principal = User::where('system_account_key', User::PRIMARY_SUPER_ADMIN_KEY)->firstOrFail();
    $other = User::factory()->create();
    $other->assignRole('SuperAdmin');

    foreach ([$principal, $other] as $actor) {
        $this->actingAs($actor)->get(route('users.show', $principal))->assertForbidden();
        $this->actingAs($actor)->get(route('users.edit', $principal))->assertForbidden();
        expect((new UserTable())->datasource()->pluck('id')->all())->not->toContain($principal->id);
    }
});

test('otros SuperAdmin pueden crear usuarios SuperAdmin', function () {
    $this->seed(DatabaseSeeder::class);
    Notification::fake();
    $actor = User::factory()->create();
    $actor->assignRole('SuperAdmin');
    $role = Role::findByName('SuperAdmin', 'web');

    Livewire::actingAs($actor)->test(UserCreate::class)
        ->set('name', 'Desarrollador adicional')
        ->set('email', 'otro.superadmin@example.org')
        ->set('roleIds', [$role->id])
        ->call('confirmSave')->assertDispatched('open-confirm-modal')
        ->call('save', 0)->assertHasNoErrors();

    $created = User::where('email', 'otro.superadmin@example.org')->firstOrFail();
    expect($created->hasRole('SuperAdmin', 'web'))->toBeTrue()
        ->and($created->isPrimarySuperAdmin())->toBeFalse();
});

test('usuarios sin permisos no pueden montar UserCreate', function () {
    Livewire::actingAs(User::factory()->create())->test(UserCreate::class)->assertForbidden();
});
