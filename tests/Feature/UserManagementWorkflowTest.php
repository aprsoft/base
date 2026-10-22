<?php

use App\Livewire\User\UserCreate;
use App\Livewire\User\UserEdit;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

test('un usuario sin permiso no puede abrir la edición', function () {
    $actor = User::factory()->create();
    $target = User::factory()->create();

    $this->actingAs($actor)->get(route('users.edit', $target))->assertForbidden();
    Livewire::actingAs($actor)->test(UserEdit::class, ['user' => $target])->assertForbidden();
});

test('el usuario protegido con ID 1 no puede editarse', function () {
    $actor = User::factory()->create();
    $actor->assignRole(Role::create(['name' => 'SuperAdmin', 'guard_name' => 'web']));

    $this->actingAs($actor)->get(route('users.edit', $actor))->assertForbidden();
});

test('la creación solicita confirmación antes de guardar', function () {
    $actor = User::factory()->create();
    $actor->assignRole(Role::create(['name' => 'SuperAdmin', 'guard_name' => 'web']));
    $role = Role::create(['name' => 'Admin', 'guard_name' => 'web']);

    Livewire::actingAs($actor)->test(UserCreate::class)
        ->set('name', 'Nuevo usuario')
        ->set('email', 'nuevo@example.com')
        ->set('roleIds', [$role->id])
        ->call('confirmSave')
        ->assertDispatched('open-confirm-modal');

    expect(User::where('email', 'nuevo@example.com')->exists())->toBeFalse();
});

test('cambiar correo actualiza roles y solicita un enlace al correo nuevo', function () {
    Notification::fake();

    $actor = User::factory()->create();
    $actor->assignRole(Role::create(['name' => 'SuperAdmin', 'guard_name' => 'web']));
    $target = User::factory()->create();
    $role = Role::create(['name' => 'Admin', 'guard_name' => 'web']);

    Livewire::actingAs($actor)->test(UserEdit::class, ['user' => $target])
        ->set('email', 'actualizado@example.com')
        ->set('roleIds', [$role->id])
        ->call('confirmSave')
        ->assertDispatched('open-confirm-modal')
        ->call('save', $target->id);

    $target->refresh();

    expect($target->email)->toBe('actualizado@example.com')
        ->and($target->email_verified_at)->toBeNull()
        ->and($target->hasRole('Admin', 'web'))->toBeTrue();

    Notification::assertSentTo($target, ResetPassword::class);
});
