<?php

namespace App\Livewire\Permission;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Spatie\Permission\Models\Permission;

class CreatePermission extends Component
{
    private function authorizeManagement(): void
    {
        Gate::authorize('create', Permission::class);
    }

    public string $permission = '';

    public function save()
    {
        $this->authorizeManagement();

        $permissionName = $this->createPermission();

        session()->flash(
            'success',
            sprintf(
                'El permiso "%s" fue creado correctamente.',
                $permissionName
            )
        );

        return redirect()->route('permissions.index');
    }

    public function saveAndCreateAnother()
    {
        $this->authorizeManagement();

        $permissionName = $this->createPermission();

        session()->flash(
            'success',
            sprintf(
                'El permiso "%s" fue creado correctamente. Puedes crear otro permiso.',
                $permissionName
            )
        );

        return redirect()->route('permissions.create');
    }

    private function createPermission(): string
    {
        $this->authorizeManagement();

        $this->permission = trim($this->permission);

        $validated = $this->validate([
            'permission' => [
                'required',
                'string',
                'max:255',
                Rule::unique(
                    config('permission.table_names.permissions'),
                    'name'
                )->where(
                    fn ($query) => $query->where('guard_name', 'web')
                ),
            ],
        ], [
            'permission.required' => 'Debes ingresar el nombre del permiso.',
            'permission.unique' => 'Ya existe un permiso con ese nombre.',
        ]);

        $permission = Permission::create([
            'name' => $validated['permission'],
            'guard_name' => 'web',
        ]);

        return $permission->name;
    }

    public function render()
    {
        $this->authorizeManagement();

        return view(
            'livewire.permission.create-permission'
        );
    }
}