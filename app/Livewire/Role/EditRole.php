<?php

namespace App\Livewire\Role;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class EditRole extends Component
{
    public int $roleId;

    public string $role = '';

    public array $permissionIds = [];

    public function mount(int $roleId): void
    {
        $role = Role::query()
            ->where('guard_name', 'web')
            ->with([
                'permissions' => fn ($query) => $query
                    ->where('guard_name', 'web'),
            ])
            ->findOrFail($roleId);

        $this->roleId = $role->id;
        $this->role = $role->name;

        $this->permissionIds = $role->permissions
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();         
    }

    public function update()
    {
        $role = Role::query()
            ->where('guard_name', 'web')
            ->findOrFail($this->roleId);

        $validated = $this->validate([
            'role' => [
                'required',
                'string',
                'max:255',
                Rule::unique(
                    config('permission.table_names.roles'),
                    'name'
                )
                    ->where(
                        fn ($query) => $query
                            ->where('guard_name', 'web')
                    )
                    ->ignore($role->id),
            ],

            'permissionIds' => [
                'required',
                'array',
                'min:1',
            ],

            'permissionIds.*' => [
                'integer',
            ],
        ], [
            'role.required' => 'Debes ingresar el nombre del rol.',
            'role.unique' => 'Ya existe un rol con ese nombre.',
            'permissionIds.required' => 'Debes seleccionar al menos un permiso.',
            'permissionIds.min' => 'Debes seleccionar al menos un permiso.',
        ]);

        $permissions = Permission::query()
            ->where('guard_name', 'web')
            ->whereIn('id', $validated['permissionIds'])
            ->get();

        if ($permissions->count() !== count($validated['permissionIds'])) {
            $this->addError(
                'permissionIds',
                'Uno o más permisos seleccionados no son válidos.'
            );

            return;
        }

        DB::transaction(function () use ($role, $validated, $permissions): void {
            $role->update([
                'name' => trim($validated['role']),
            ]);

            $role->syncPermissions($permissions);
        });

        session()->flash('success', 'Rol actualizado correctamente.');

        return redirect()->route('roles.index');
    }

    public function render()
    {
        $permissions = Permission::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get(['id', 'name']);

        $selectedPermissions = $permissions
            ->filter(
                fn (Permission $permission) => in_array(
                    (int) $permission->id,
                    $this->permissionIds,
                    true
                )
            )
            ->values();

        return view('livewire.role.edit-role', [
            'permissions' => $permissions,
            'selectedPermissions' => $selectedPermissions,
        ]);
    }
}