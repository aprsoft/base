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

            /*
             * Buscar cuenta principal ya identificada.
             */
            $principal = User::withTrashed()
                ->where(
                    'system_account_key',
                    User::PRIMARY_SUPER_ADMIN_KEY
                )
                ->first();

            /*
             * Si existe, no modificar sus datos,
             * contraseña ni roles.
             */
            if ($principal !== null) {
                if (
                    $principal->trashed()
                    || ! $principal->hasRole('SuperAdmin', 'web')
                ) {
                    throw new RuntimeException(
                        'La cuenta principal está suspendida o ha perdido su rol SuperAdmin.'
                    );
                }

                return;
            }

            /*
             * Instalación existente:
             *
             * Nunca identificar automáticamente
             * al principal solo por su ID o correo.
             */
            if (User::withTrashed()->exists()) {
                throw new RuntimeException(
                    'Existen usuarios, pero no hay una cuenta principal identificada. Verifica manualmente la cuenta SuperAdmin antes de ejecutar este seeder.'
                );
            }

            /*
             * Credenciales temporales para desarrollo.
             *
             * Se mantiene "password" según la decisión
             * actual del proyecto.
             *
             * No habilitar este aprovisionamiento
             * con credenciales conocidas en producción.
             */
            if (! app()->environment(['local', 'testing'])) {
                throw new RuntimeException(
                    'El aprovisionamiento inicial con credenciales de desarrollo no está permitido en este entorno.'
                );
            }

            /*
             * Crear SuperAdministrador principal.
             */
            $principal = User::factory()->create([
                'name' => 'SuperAdministrador',
                'email' => 'superadmin@aprsoft.cl',
            ]);

            /*
             * Asignación interna protegida.
             */
            $principal->forceFill([
                'system_account_key' =>
                    User::PRIMARY_SUPER_ADMIN_KEY,
            ])->save();

            /*
             * Asignar el rol SuperAdmin.
             */
            $principal->assignRole('SuperAdmin');

            /*
             * Crear Administrador de desarrollo.
             */
            User::factory()->create([
                'name' => 'Administrador',
                'email' => 'admin@aprsoft.cl',
            ])->assignRole('Admin');

            /*
             * Usuarios de prueba.
             */
            User::factory(9)->create();
        });
    }
}
