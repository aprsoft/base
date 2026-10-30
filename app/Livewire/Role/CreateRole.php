<?php

namespace App\Livewire\Role;

use App\Support\Permissions\PermissionCatalog;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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
        $this->role = trim($this->role);

        $validated = $this->validate([
            'role' => [
                'required',
                'string',
                'max:255',
                static function (string $attribute, mixed $value, Closure $fail): void {
                    if (is_string($value) && strcasecmp($value, 'SuperAdmin') === 0) {
                        $fail('El nombre SuperAdmin está reservado.');
                    }
                },
                Rule::unique(config('permission.table_names.roles'), 'name')
                    ->where(fn ($query) => $query->where('guard_name', 'web')),
            ],
            'permissionIds' => ['required', 'array', 'min:1'],
            'permissionIds.*' => [
                'integer',
                'distinct',
                Rule::in($this->assignablePermissions()->modelKeys()),
            ],
        ], [
            'role.required' => 'Debes ingresar el nombre del rol.',
            'role.unique' => 'Ya existe un rol con ese nombre.',
            'permissionIds.required' => 'Debes seleccionar al menos un permiso.',
            'permissionIds.min' => 'Debes seleccionar al menos un permiso.',
            'permissionIds.*.distinct' => 'No puedes seleccionar un permiso más de una vez.',
            'permissionIds.*.in' => 'Uno de los permisos seleccionados no está permitido.',
        ]);

        DB::transaction(function () use ($validated): void {
            $requestedIds = array_map('intval', $validated['permissionIds']);

            // Revalidar dentro de la transacción para no usar permisos revocados.
            $permissions = $this->assignablePermissions()
                ->whereIn('id', $requestedIds);

            if ($permissions->count() !== count($requestedIds)) {
                throw ValidationException::withMessages([
                    'permissionIds' => 'Tus permisos cambiaron. Recarga el formulario.',
                ]);
            }

            $role = Role::create([
                'name' => $validated['role'],
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

        $catalog = app(PermissionCatalog::class);
        $groups = $catalog->groups($permissions);

        return view('livewire.role.create-role', [
            'permissions' => $permissions,
            'selectedPermissions' => $selectedPermissions,
            'permissionGroups' => $groups,
            'groupsForAlpine' => $catalog->forAlpine($groups),
        ]);
    }
}