<?php

declare(strict_types=1);

namespace App\Services\Central\Employee;

use App\Models\Central\Employee;
use App\Models\Central\User;
use App\Services\Central\ErrorLog\ErrorLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

final class EmployeeUserCreator
{
    public function __construct(
        private readonly ErrorLogger $errorLogger,
    ) {
    }

    public function create(
        array $employeeData,
        ?string $email = null
    ): Employee {
        $connection = config('tenancy.database.central_connection');

        if (! is_string($connection) || $connection === '') {
            throw new RuntimeException(
                'La conexión central no está configurada.'
            );
        }

        try {
            $email = $this->normalizeEmail($email);

            return DB::connection($connection)->transaction(
                function () use (
                    $connection,
                    $employeeData,
                    $email
                ): Employee {
                    $user = new User();

                    $user->setConnection($connection);

                    $user->fill([
                        'email' => $email,
                        'password' => Str::random(64),
                        'is_active' => false,
                    ]);

                    $user->save();

                    $employee = new Employee();

                    $employee->setConnection($connection);

                    $employee->fill($employeeData);
                    $employee->user_id = $user->id;

                    $employee->save();

                    $employee->setRelation('user', $user);

                    return $employee;
                }
            );
        } catch (Throwable $exception) {
            $this->errorLogger->report($exception, [
                'operation' => 'central.employee.create',
                'employee' => $this->safeEmployeeContext($employeeData),
                'email' => $email,
            ]);

            throw $exception;
        }
    }

    private function normalizeEmail(?string $email): ?string
    {
        if ($email === null) {
            return null;
        }

        $email = Str::lower(trim($email));

        return $email !== '' ? $email : null;
    }

    private function safeEmployeeContext(array $employeeData): array
    {
        return [
            'rut' => $employeeData['rut'] ?? null,
            'name' => $employeeData['name'] ?? null,
            'state' => $employeeData['state'] ?? null,
        ];
    }
}