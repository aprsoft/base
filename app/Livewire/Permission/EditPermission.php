<?php

declare(strict_types=1);

namespace App\Livewire\Permission;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Spatie\Permission\Models\Permission;

class EditPermission extends Component
{
    #[Locked]
    public int $permissionId;

    #[Locked]
    public ?int $pendingDeleteId = null;

    public string $permission = '';

    public function mount(int $permissionId): void
    {
        Gate::authorize('viewAny', Permission::class);

        $permission = Permission::query()
            ->where('guard_name', 'web')
            ->findOrFail($permissionId);

        Gate::authorize('update', $permission);

        abort_if($permission->roles()->exists(), 403);

        $this->permissionId = (int) $permission->id;
        $this->permission = $permission->name;
    }

    private function editablePermission(): Permission
    {
        Gate::authorize('viewAny', Permission::class);

        $permission = Permission::query()
            ->where('guard_name', 'web')
            ->findOrFail($this->permissionId);

        Gate::authorize('update', $permission);

        abort_if($permission->roles()->exists(), 403);

        return $permission;
    }

    public function update()
    {
        $this->editablePermission();

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
                    ->where(fn ($query) =>
                        $query->where('guard_name', 'web')
                    )
                    ->ignore($this->permissionId),
            ],
        ], [
            'permission.required' => 'Debes ingresar el nombre del permiso.',
            'permission.unique' => 'Ya existe un permiso con ese nombre.',
        ]);

        try {
            DB::transaction(function () use ($validated): void {
                $permission = Permission::query()
                    ->where('guard_name', 'web')
                    ->lockForUpdate()
                    ->findOrFail($this->permissionId);

                Gate::authorize('update', $permission);

                abort_if($permission->roles()->exists(), 403);

                $permission->update([
                    'name' => $validated['permission'],
                ]);
            });
        } catch (QueryException $exception) {
            report($exception);

            $this->addError(
                'permission',
                'No fue posible actualizar el permiso.'
            );

            return;
        }

        session()->flash(
            'success',
            'Permiso actualizado correctamente.'
        );

        return redirect()->route('permissions.index');
    }

    public function confirmDelete(): void
    {
        $permission = $this->editablePermission();

        Gate::authorize('delete', $permission);

        abort_if(
            $permission->roles()->exists()
                || $permission->users()->exists(),
            403
        );

        $this->pendingDeleteId = (int) $permission->id;

        $this->dispatch(
            'open-confirm-modal',
            id: $permission->id,
            name: $permission->name,
            confirmEvent: 'permission-edit-delete-confirmed',
            title: 'Eliminar permiso',
            message: '¿Confirmas que deseas eliminar definitivamente este permiso?',
            warning: 'Esta operación no se puede deshacer.',
            confirmLabel: 'Sí, eliminar'
        );
    }

    #[On('permission-edit-delete-confirmed')]
    public function delete(int $id): void
    {
        abort_unless(
            $id === $this->permissionId
                && $id === $this->pendingDeleteId,
            403
        );

        $this->pendingDeleteId = null;

        try {
            $name = DB::transaction(function () use ($id): string {
                $permission = Permission::query()
                    ->where('guard_name', 'web')
                    ->lockForUpdate()
                    ->findOrFail($id);

                Gate::authorize('update', $permission);
                Gate::authorize('delete', $permission);

                abort_if(
                    $permission->roles()->exists()
                        || $permission->users()->exists(),
                    403
                );

                $name = $permission->name;

                $permission->delete();

                return $name;
            });
        } catch (QueryException $exception) {
            report($exception);

            $this->addError(
                'permission',
                'No fue posible eliminar el permiso.'
            );

            return;
        }

        session()->flash(
            'success',
            "El permiso \"{$name}\" fue eliminado correctamente."
        );

        $this->redirectRoute('permissions.index');
    }

    public function render()
    {
        $permission = $this->editablePermission();

        return view('livewire.permission.edit-permission', [
            'canDelete' => Gate::allows('delete', $permission)
                && ! $permission->roles()->exists()
                && ! $permission->users()->exists(),
        ]);
    }
}