<?php

use App\Livewire\User\UserEdit;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

function lifecycleActor(array $permissions = []): User
{
    $role = Role::firstOrCreate(['name' => 'LifecycleOperator', 'guard_name' => 'web']);

    foreach ($permissions as $name) {
        $permission = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        $role->givePermissionTo($permission);
    }

    $actor = User::factory()->create();
    $actor->assignRole($role);

    return $actor;
}

test('an unauthorized user cannot suspend or restore accounts', function () {
    User::factory()->create(); // ID 1
    $actor = lifecycleActor();
    $target = User::factory()->create();

    $this->actingAs($actor);

    expect(Gate::denies('suspend', $target))->toBeTrue();

    Livewire::actingAs($actor)
        ->test(UserEdit::class, ['user' => $target])
        ->assertForbidden();

    $target->delete();

    expect(Gate::denies('restore', $target))->toBeTrue();

    Livewire::actingAs($actor)
        ->test(UserEdit::class, ['user' => $target])
        ->assertForbidden();

    expect(User::withTrashed()->findOrFail($target->id)->trashed())->toBeTrue();
});

test('an authorized operator can suspend an account with confirmation', function () {
    User::factory()->create(); // ID 1
    $actor = lifecycleActor(['users.index', 'users.edit', 'users.suspend']);
    $target = User::factory()->create();

    Livewire::actingAs($actor)
        ->test(UserEdit::class, ['user' => $target])
        ->call('confirmSuspend')
        ->assertDispatched('open-confirm-modal')
        ->call('suspend', $target->id)
        ->assertHasNoErrors();

    expect(User::withTrashed()->findOrFail($target->id)->trashed())->toBeTrue();
});

test('an authorized operator can restore a suspended account', function () {
    User::factory()->create(); // ID 1
    $actor = lifecycleActor(['users.index', 'users.restore']);
    $target = User::factory()->create();
    $target->delete();

    Livewire::actingAs($actor)
        ->test(UserEdit::class, ['user' => $target])
        ->call('confirmRestore')
        ->assertDispatched('open-confirm-modal')
        ->call('restore', $target->id)
        ->assertHasNoErrors();

    expect(User::find($target->id))->not->toBeNull();
});

test('the primary and own accounts cannot be suspended', function () {
    $primary = User::factory()->create();
    $actor = lifecycleActor(['users.index', 'users.edit', 'users.suspend']);

    $this->actingAs($actor);

    expect(Gate::denies('suspend', $primary))->toBeTrue()
        ->and(Gate::denies('suspend', $actor))->toBeTrue();

    Livewire::actingAs($actor)
        ->test(UserEdit::class, ['user' => $primary])
        ->assertForbidden();

    expect(User::withTrashed()->findOrFail($primary->id)->trashed())->toBeFalse();
});

test('deletion remains disabled even for SuperAdmin', function () {
    User::factory()->create();
    $actor = User::factory()->create();
    $actor->assignRole(Role::firstOrCreate(['name' => 'SuperAdmin', 'guard_name' => 'web']));
    $target = User::factory()->create();

    $this->actingAs($actor);

    expect(Gate::denies('delete', $target))->toBeTrue()
        ->and(Gate::denies('forceDelete', $target))->toBeTrue();
});
