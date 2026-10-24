<?php

use App\Models\User;
use App\Support\Security\ProtectedSystemAccount;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Hash;

test('el seeder crea una sola cuenta principal', function () {
    $this->seed(DatabaseSeeder::class);

    $principal = User::query()
        ->where(
            'system_account_key',
            User::PRIMARY_SUPER_ADMIN_KEY
        )
        ->firstOrFail();

    expect($principal->isPrimarySuperAdmin())->toBeTrue()
        ->and($principal->hasRole('SuperAdmin', 'web'))->toBeTrue()
        ->and(Hash::check('password', $principal->password))
            ->toBeTrue();

    $totalUsers = User::count();

    $this->seed(DatabaseSeeder::class);

    expect(User::count())->toBe($totalUsers);

    expect(
        User::where(
            'system_account_key',
            User::PRIMARY_SUPER_ADMIN_KEY
        )->count()
    )->toBe(1);
});

test('un SuperAdmin secundario no es la cuenta principal', function () {
    $this->seed(DatabaseSeeder::class);

    $secundario = User::factory()->create();

    $secundario->assignRole('SuperAdmin');

    expect($secundario->hasRole('SuperAdmin', 'web'))
        ->toBeTrue()
        ->and($secundario->isPrimarySuperAdmin())
        ->toBeFalse()
        ->and(ProtectedSystemAccount::isPrimary($secundario))
        ->toBeFalse();
});

test('la base de datos impide dos cuentas principales', function () {
    $this->seed(DatabaseSeeder::class);

    $segundo = User::factory()->create();

    expect(fn () => $segundo->forceFill([
        'system_account_key' => User::PRIMARY_SUPER_ADMIN_KEY,
    ])->save())->toThrow(QueryException::class);
});

test('el seeder no adopta usuarios existentes automáticamente', function () {
    User::factory()->create([
        'email' => 'otro@example.com',
    ]);

    $this->seed(
        \Database\Seeders\RolePermissionSeeder::class
    );

    expect(
        fn () => $this->seed(
            \Database\Seeders\UserSeeder::class
        )
    )->toThrow(RuntimeException::class);
});
