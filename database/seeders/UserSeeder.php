<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $principal = User::withTrashed()
                ->where('system_account_key', User::PRIMARY_SUPER_ADMIN_KEY)
                ->first();

            if ($principal !== null) {
                $this->assertPrincipalValid($principal);
                return;
            }

            // Migración de instalaciones anteriores, solo si se confirma
            // correo original y rol. Nunca suponer que id=1 es el principal.
            $legacy = User::withTrashed()
                ->where('email', 'superadmin@aprsoft.cl')
                ->first();

            if ($legacy !== null) {
                $this->assertPrincipalValid($legacy);
                $legacy->forceFill([
                    'system_account_key' => User::PRIMARY_SUPER_ADMIN_KEY,
                ])->save();
                return;
            }

            if (User::withTrashed()->exists()) {
                throw new RuntimeException(
                    'No se identificó al SuperAdmin principal. Revisar la cuenta existente antes de ejecutar el seeder.'
                );
            }

            // Uso temporal de "password" solo para desarrollo.
            // Se requiere aprovisionamiento seguro antes de producción.
            $principal = User::factory()->create([
                'name' => 'SuperAdministrador',
                'email' => 'superadmin@aprsoft.cl',
            ]);

            $principal->forceFill([
                'system_account_key' => User::PRIMARY_SUPER_ADMIN_KEY,
            ])->save();
            $principal->assignRole('SuperAdmin');

            if (app()->environment(['local', 'testing'])) {
                User::factory()->create([
                    'name' => 'Administrador',
                    'email' => 'admin@aprsoft.cl',
                ])->assignRole('Admin');

                User::factory(9)->create();
            }
        });
    }

    private function assertPrincipalValid(User $user): void
    {
        if ($user->trashed() || ! $user->hasRole('SuperAdmin', 'web')) {
            throw new RuntimeException(
                'La cuenta principal está suspendida o no conserva el rol SuperAdmin. Se requiere revisión manual.'
            );
        }
    }
}
