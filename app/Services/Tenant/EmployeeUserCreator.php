<?php

declare(strict_types=1);

namespace App\Services\Tenant;

use App\Models\Tenant\Employee;
use App\Models\Tenant\User;
use App\Services\Central\ErrorLog\ErrorLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use App\Events\Tenant\UserCreated;

use Throwable;

final class EmployeeUserCreator
{
    public function __construct(
        private readonly ErrorLogger $errorLogger,
    ) {
    }

    public function create(
        array $employeeData,
        string $email,
        string $plainPassword,
        bool $isActive,
        array $roleIds = [],
    ): Employee {
        try {

            // throw new \RuntimeException(
            // 'PRUEBA CONTROLADA: error al crear empleado.'
            // );

            $email = Str::lower(trim($email));

            return DB::connection('tenant')->transaction(
                function () use (
                    $employeeData,
                    $email,
                    $plainPassword,
                    $isActive,
                    $roleIds,
                ): Employee {
                    $user = new User();

                    $user->setConnection('tenant');

                    $user->fill([
                        'email' => $email,
                        'password' => $plainPassword,
                        'is_active' => $isActive,
                    ]);

                    $user->save();

                    $employee = new Employee();

                    $employee->setConnection('tenant');
                    $employee->fill($employeeData);
                    $employee->user_id = $user->id;

                    $employee->save();

                    if ($isActive) {
                        $roles = Role::query()
                            ->where('guard_name', 'tenant')
                            ->where('name', '!=', 'inactive')
                            ->whereIn('id', $roleIds)
                            ->get();

                        if ($roles->count() !== count($roleIds)) {
                            throw new \InvalidArgumentException(
                                'Uno o más roles seleccionados no son válidos.'
                            );
                        }

                        $user->syncRoles($roles);
                    } else {
                        // $inactiveRole = Role::query()
                        //     ->where('guard_name', 'tenant')
                        //     ->where('name', 'inactive')
                        //     ->firstOrFail();

                        // $user->syncRoles([$inactiveRole]);
                          $user->syncRoles([]);
                    }

                    $employee->setRelation('user', $user);

                    return $employee;
                }
            );
        } catch (Throwable $exception) {
            $this->errorLogger->report($exception, [
                'operation' => 'tenant.employee.create',
                // 'tenant_id' => tenant()?->getTenantKey(),
                'employee' => $this->safeEmployeeContext($employeeData),
                'email' => $email,
                'is_active' => $isActive,
            ]);

            throw $exception;
        }
    }

    private function safeEmployeeContext(array $employeeData): array
    {
        return [
            'rut' => $employeeData['rut'] ?? null,
            'nombres' => $employeeData['nombres'] ?? null,
            'state' => $employeeData['state'] ?? null,
        ];
    }
}