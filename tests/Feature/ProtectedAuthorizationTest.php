<?php

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;

test('primary account is never administratively accessible even by SuperAdmin', function () {
    $primary = User::factory()->create();
    $actor = User::factory()->create();
    Role::firstOrCreate(['name' => 'SuperAdmin', 'guard_name' => 'web']);
    $actor->assignRole('SuperAdmin');

    $this->actingAs($actor, 'web');

    expect(Gate::denies('view', $primary))->toBeTrue();
    expect(Gate::denies('update', $primary))->toBeTrue();
    expect(Gate::denies('suspend', $primary))->toBeTrue();
    expect(Gate::denies('delete', $primary))->toBeTrue();

    $this->get('/users/'.$primary->id)->assertForbidden();
    $this->get('/users/'.$primary->id.'/edit')->assertForbidden();
});

test('primary account cannot delete its profile but can change its password', function () {
    $primary = User::factory()->create();
    Role::firstOrCreate(['name' => 'SuperAdmin', 'guard_name' => 'web']);
    $primary->assignRole('SuperAdmin');

    $this->actingAs($primary, 'web');

    expect(Gate::denies('profile.update'))->toBeTrue();
    expect(Gate::allows('profile.changePassword'))->toBeTrue();

    $this->delete('/settings/profile')->assertNotFound();
    expect($primary->fresh()?->trashed())->toBeFalse();
});

test('authenticated users without error permissions cannot view error logs', function () {
    $actor = User::factory()->create();
    $this->actingAs($actor, 'web');

    $this->get('/errors')->assertForbidden();
    $this->get('/errors/1')->assertNotFound();
});

test('secondary SuperAdmin cannot delete its own profile', function () {
    User::factory()->create();
    $actor = User::factory()->create();
    Role::firstOrCreate(['name' => 'SuperAdmin', 'guard_name' => 'web']);
    $actor->assignRole('SuperAdmin');

    $this->actingAs($actor, 'web');

    $this->delete('/settings/profile')->assertNotFound();
    expect($actor->fresh()?->trashed())->toBeFalse();
});
