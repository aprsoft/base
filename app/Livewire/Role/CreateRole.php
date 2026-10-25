<?php

namespace App\Livewire\Role;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CreateRole extends Component
{
    private function authorizeManagement(): void
    {
        Gate::authorize('create', Role::class);
    }

    private function assignablePermissions()
    {
        $actor = auth('web')->user();
        abort_unless($actor, 403);

        $query = Permission::query()->where('guard_name', 'web');

        if (! $actor->hasRole('SuperAdmin', 'web')) {
            $query->whereIn('name', $actor->getAllPermissions()
                ->where('guard_name', 'web')->pluck('name')->all());
        }

        return $query->orderBy('name')->get();
    }

    public string $role = '';

    public array $permissionIds = [];

    public function save()
    {
        $this->authorizeManagement();

        $validated = $this->validate([
            'role' => [
                'required',
                'string',
                'max:255',
                Rule::unique(
                    config('permission.table_names.roles'),
                    'name'
                )->where(
                    fn ($query) => $query->where('guard_name', 'web')
                ),
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

        $permissions = $this->assignablePermissions()
            ->whereIn('id', $validated['permissionIds']);

        if ($permissions->count() !== count($validated['permissionIds'])) {
            $this->addError(
                'permissionIds',
                'Uno o más permisos seleccionados no son válidos.'
            );

            return;
        }

        DB::transaction(function () use ($validated, $permissions): void {
            $role = Role::create([
                'name' => trim($validated['role']),
                'guard_name' => 'web',
            ]);

            $role->syncPermissions($permissions);
        });

        session()->flash('success', 'Rol creado correctamente.');

        return redirect()->route('roles.index');
    }

    public function render()
    {
        $this->authorizeManagement();

        $permissions = $this->assignablePermissions();

        $selectedPermissions = $permissions
            ->filter(
                fn (Permission $permission) => in_array(
                    (int) $permission->id,
                    $this->permissionIds,
                    true
                )
            )
            ->values();

        return view('livewire.role.create-role', [
            'permissions' => $permissions,
            'selectedPermissions' => $selectedPermissions,
        ]);
    }
}