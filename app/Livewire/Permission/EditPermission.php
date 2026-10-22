<?php

namespace App\Livewire\Permission;

use App\Services\Central\ErrorLog\ErrorLogger;
use App\Services\Permission\UpdatePermissionService;
use DomainException;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Throwable;

class EditPermission extends Component
{
    public int $permissionId;

    public string $permission = '';

    public function mount(int $permissionId): void
    {
        $permission = Permission::query()
            ->where('guard_name', 'web')
            ->findOrFail($permissionId);

        $this->permissionId = $permission->id;
        $this->permission = $permission->name;
    }

    public function update(
        UpdatePermissionService $updatePermission,
        ErrorLogger $errorLogger
    ) {
        try {
            $this->permission = trim($this->permission);

            $validated = $this->validate([
                'permission' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique(
                        config('permission.table_names.permissions'),
                        'name'
                    )
                        ->where(
                            fn ($query) => $query
                                ->where('guard_name', 'web')
                        )
                        ->ignore($this->permissionId),
                ],
            ], [
                'permission.required' => 'Debes ingresar el nombre del permiso.',
                'permission.unique' => 'Ya existe un permiso con ese nombre.',
            ]);

            $permissionName = $updatePermission->update(
                $this->permissionId,
                $validated['permission']
            );

            session()->flash(
                'success',
                sprintf(
                    'El permiso "%s" fue actualizado correctamente.',
                    $permissionName
                )
            );

            return redirect()->route('central.permissions.index');
        } catch (ValidationException $exception) {
            /*
             * Registramos el error de validación en error_logs,
             * pero relanzamos la excepción para que Livewire conserve
             * su comportamiento normal y muestre los errores del formulario.
             */
            $errorLogger->report(
                $exception,
                [
                    'operation' => 'permission.update',
                    'permission_id' => $this->permissionId,
                    'error_type' => 'validation',
                    'validation_errors' => $exception->errors(),
                ]
            );

            throw $exception;
        } catch (DomainException $exception) {
            $errorLogger->report(
                $exception,
                [
                    'operation' => 'permission.update',
                    'permission_id' => $this->permissionId,
                    'error_type' => 'business',
                ]
            );

            session()->flash(
                'error',
                $exception->getMessage()
            );

            return redirect()->route('central.permissions.index');
        } catch (QueryException $exception) {
            $errorLogger->report(
                $exception,
                [
                    'operation' => 'permission.update',
                    'permission_id' => $this->permissionId,
                    'error_type' => 'database',
                ]
            );

            session()->flash(
                'error',
                'Ocurrió un error de base de datos al actualizar el permiso.'
            );

            return redirect()->route('central.permissions.index');
        } catch (Throwable $exception) {
            $errorLogger->report(
                $exception,
                [
                    'operation' => 'permission.update',
                    'permission_id' => $this->permissionId,
                    'error_type' => 'unexpected',
                ]
            );

            session()->flash(
                'error',
                'Ocurrió un error inesperado al actualizar el permiso.'
            );

            return redirect()->route('central.permissions.index');
        }
    }

    public function render()
    {
        return view('livewire.permission.edit-permission');
    }
}