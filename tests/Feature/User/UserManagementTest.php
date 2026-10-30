<?php

use App\Livewire\User\UserCreate;
use App\Livewire\User\UserEdit;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Password;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $this->superAdmin = User::factory()->create();
    $this->superAdmin->assignRole(Role::firstOrCreate([
        'name' => 'SuperAdmin',
        'guard_name' => 'web',
    ]));
});

it('redirige a invitados antes de entrar a usuarios', function (): void {
    $this->get(route('users.index'))->assertRedirect(route('login'));
    $this->get(route('users.create'))->assertRedirect(route('login'));
});

it('niega el modulo usuarios a una cuenta sin autorizacion', function (): void {
    $actor = User::factory()->create();

    $this->actingAs($actor)->get(route('users.index'))->assertForbidden();
    $this->actingAs($actor)->get(route('users.create'))->assertForbidden();
});

it('permite abrir el formulario de usuarios a SuperAdmin', function (): void {
    Livewire::actingAs($this->superAdmin)
        ->test(UserCreate::class)
        ->assertStatus(200);
});

it('valida los datos obligatorios antes de crear un usuario', function (): void {
    Livewire::actingAs($this->superAdmin)
        ->test(UserCreate::class)
        ->call('confirmSave')
        ->assertHasErrors(['name', 'email', 'roleIds']);
});

it('impide registrar otro usuario con correo duplicado', function (): void {
    $existing = User::factory()->create();

    Livewire::actingAs($this->superAdmin)
        ->test(UserCreate::class)
        ->set('name', 'Nuevo usuario')
        ->set('email', $existing->email)
        ->set('roleIds', [Role::create([
            'name' => 'Operador',
            'guard_name' => 'web',
        ])->id])
        ->call('confirmSave')
        ->assertHasErrors(['email' => 'unique']);
});

it('crea un usuario con rol autorizado y solicita el enlace de acceso', function (): void {
    $role = Role::create(['name' => 'Operador', 'guard_name' => 'web']);
    Password::shouldReceive('sendResetLink')
        ->once()
        ->andReturn(Password::RESET_LINK_SENT);

    Livewire::actingAs($this->superAdmin)
        ->test(UserCreate::class)
        ->set('name', 'Nuevo operador')
        ->set('email', 'nuevo.operador@example.com')
        ->set('roleIds', [(int) $role->id])
        ->call('save', 0)
        ->assertHasNoErrors()
        ->assertRedirectToRoute('users.index');

    $created = User::where('email', 'nuevo.operador@example.com')->firstOrFail();

    expect($created->name)->toBe('Nuevo operador')
        ->and($created->hasRole('Operador', 'web'))->toBeTrue();
});

it('no permite crear usuarios con un rol que otorgue permisos ajenos', function (): void {
    $actor = User::factory()->create();
    $actor->givePermissionTo(Permission::create([
        'name' => 'users.create',
        'guard_name' => 'web',
    ]));

    $privilegedRole = Role::create([
        'name' => 'Finanzas',
        'guard_name' => 'web',
    ]);
    $privilegedRole->givePermissionTo(Permission::create([
        'name' => 'facturas.destroy',
        'guard_name' => 'web',
    ]));

    Livewire::actingAs($actor)
        ->test(UserCreate::class)
        ->assertViewHas('roles', fn ($roles): bool =>
            ! $roles->contains('id', $privilegedRole->id)
        )
        ->set('name', 'Intento de escalamiento')
        ->set('email', 'intento@example.com')
        ->set('roleIds', [(int) $privilegedRole->id])
        ->call('confirmSave')
        ->assertHasErrors(['roleIds.0' => 'in']);

    expect(User::where('email', 'intento@example.com')->exists())->toBeFalse();
});

it('impide editar o suspender la propia cuenta', function (): void {
    Livewire::actingAs($this->superAdmin)
        ->test(UserEdit::class, ['user' => $this->superAdmin])
        ->assertForbidden();
});

it('protege la cuenta principal de identificador uno', function (): void {
    // El primer usuario del test es la cuenta principal.
    expect((int) $this->superAdmin->id)->toBe(1);

    $anotherAdmin = User::factory()->create();
    $anotherAdmin->assignRole(Role::findByName('SuperAdmin', 'web'));

    expect(Gate::forUser($anotherAdmin)->allows('view', $this->superAdmin))->toBeFalse()
        ->and(Gate::forUser($anotherAdmin)->allows('update', $this->superAdmin))->toBeFalse()
        ->and(Gate::forUser($anotherAdmin)->allows('suspend', $this->superAdmin))->toBeFalse();
});

it('suspende y restaura un usuario mediante borrado logico', function (): void {
    $target = User::factory()->create();

    Livewire::actingAs($this->superAdmin)
        ->test(UserEdit::class, ['user' => $target])
        ->call('suspend', (int) $target->id)
        ->assertRedirectToRoute('users.index');

    expect(User::withTrashed()->findOrFail($target->id)->trashed())->toBeTrue();

    Livewire::actingAs($this->superAdmin)
        ->test(UserEdit::class, [
            'user' => User::withTrashed()->findOrFail($target->id),
        ])
        ->call('restore', (int) $target->id)
        ->assertRedirectToRoute('users.index');

    expect(User::withTrashed()->findOrFail($target->id)->trashed())->toBeFalse();
});

it('rechaza el identificador equivocado al suspender usuarios', function (): void {
    $target = User::factory()->create();
    $other = User::factory()->create();

    Livewire::actingAs($this->superAdmin)
        ->test(UserEdit::class, ['user' => $target])
        ->call('suspend', (int) $other->id)
        ->assertForbidden();

    expect(User::withTrashed()->findOrFail($target->id)->trashed())->toBeFalse()
        ->and(User::withTrashed()->findOrFail($other->id)->trashed())->toBeFalse();
});

it('impide el borrado fisico de usuarios mediante la politica', function (): void {
    $target = User::factory()->create();

    expect(Gate::forUser($this->superAdmin)->allows('delete', $target))->toBeFalse()
        ->and(Gate::forUser($this->superAdmin)->allows('forceDelete', $target))->toBeFalse();
});
