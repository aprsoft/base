
<?php

declare(strict_types=1);

namespace App\Livewire\Role;

use App\Support\Permissions\PermissionCatalog;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class EditRole extends Component
{
    #[Locked]
    public int $roleId;

    public string $role = '';

    public array $permissionIds = [];

    /**
     * Obtiene y autoriza el rol.
     */
    private function editableRole(bool $lock = false): Role
    {
        Gate::authorize('viewAny', Role::class);

        $query = Role::query()->where('guard_name', 'web');

        if ($lock) {
            $query->lockForUpdate();
        }

        $role = $query->findOrFail($this->roleId);

        // Protección adicional del rol reservado.
        abort_if(
            strcasecmp($role->name, 'SuperAdmin') === 0,
            403
        );

        Gate::authorize('update', $role);

        return $role;
    }

    /**
     * Permisos que el usuario autenticado puede administrar.
     */
    private function assignablePermissions(): Collection
    {
        $actor = auth('web')->user();

        abort_unless($actor, 403);

        $query = Permission::query()
            ->where('guard_name', 'web');

        if (! $actor->hasRole('SuperAdmin', 'web')) {
            $names = $actor->getAllPermissions()
                ->where('guard_name', 'web')
                ->pluck('name')
                ->all();

            $query->whereIn('name', $names);
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Inicializa el formulario.
     */
    public function mount(int $roleId): void
    {
        $this->roleId = $roleId;

        $role = $this->editableRole()->load('permissions');

        $allowedIds = array_map(
            'intval',
            $this->assignablePermissions()->modelKeys()
        );

        $this->role = $role->name;

        // Mostrar solamente permisos administrables.
        $this->permissionIds = $role->permissions
            ->filter(
                fn (Permission $permission): bool => in_array(
                    (int) $permission->id,
                    $allowedIds,
                    true
                )
            )
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->values()
            ->all();
    }

    /**
     * Actualiza el rol y sus permisos.
     */
    public function update()
    {
        // Verificar autorización antes de validar.
        $this->editableRole();

        $this->role = trim($this->role);

        $validated = $this->validate([
            'role' => [
                'required',
                'string',
                'max:255',

                // Impedir utilizar el nombre reservado.
                static function (
                    string $attribute,
                    mixed $value,
                    Closure $fail
                ): void {
                    if (
                        is_string($value)
                        && strcasecmp($value, 'SuperAdmin') === 0
                    ) {
                        $fail(
                            'El nombre SuperAdmin está reservado.'
                        );
                    }
                },

                Rule::unique(
                    config('permission.table_names.roles'),
                    'name'
                )
                    ->where(
                        fn ($query) => $query
                            ->where('guard_name', 'web')
                    )
                    ->ignore($this->roleId),
            ],

            'permissionIds' => [
                'array',
            ],

            'permissionIds.*' => [
                'integer',
                'distinct',
                Rule::in(
                    $this->assignablePermissions()->modelKeys()
                ),
            ],
        ], [
            'role.required' =>
                'Debes ingresar el nombre del rol.',

            'role.unique' =>
                'Ya existe un rol con ese nombre.',

            'permissionIds.array' =>
                'La selección de permisos no es válida.',

            'permissionIds.*.integer' =>
                'Uno de los permisos seleccionados no es válido.',

            'permissionIds.*.distinct' =>
                'No puedes seleccionar un permiso más de una vez.',

            'permissionIds.*.in' =>
                'Uno de los permisos seleccionados no está permitido.',
        ]);

        DB::transaction(function () use ($validated): void {

            // Bloquear el rol y revalidar autorización.
            $role = $this->editableRole(lock: true);

            // Consultar los permisos actuales del editor.
            $allowedIds = array_map(
                'intval',
                $this->assignablePermissions()->modelKeys()
            );

            $requestedIds = array_map(
                'intval',
                $validated['permissionIds']
            );

            // Evitar modificaciones con permisos revocados.
            if (array_diff($requestedIds, $allowedIds) !== []) {
                throw ValidationException::withMessages([
                    'permissionIds' =>
                        'Tus permisos cambiaron. Recarga el formulario.',
                ]);
            }

            // Consultar los permisos actuales del rol.
            $role->load('permissions');

            /*
             * Conservar los permisos existentes que
             * este editor no puede administrar.
             */
            $preservedIds = $role->permissions
                ->reject(
                    fn (Permission $permission): bool => in_array(
                        (int) $permission->id,
                        $allowedIds,
                        true
                    )
                )
                ->pluck('id')
                ->map(fn ($id): int => (int) $id)
                ->all();

            /*
             * Combinar permisos protegidos con
             * los seleccionados por el editor.
             */
            $finalIds = array_values(array_unique([
                ...$preservedIds,
                ...$requestedIds,
            ]));

            // Evitar roles sin permisos.
            if ($finalIds === []) {
                throw ValidationException::withMessages([
                    'permissionIds' =>
                        'El rol debe conservar al menos un permiso.',
                ]);
            }

            // Obtener únicamente permisos del guard web.
            $permissions = Permission::query()
                ->where('guard_name', 'web')
                ->whereIn('id', $finalIds)
                ->get();

            // Comprobar que ninguno haya desaparecido.
            if ($permissions->count() !== count($finalIds)) {
                throw ValidationException::withMessages([
                    'permissionIds' =>
                        'La selección de permisos cambió. Recarga el formulario.',
                ]);
            }

            // Actualizar nombre del rol.
            $role->update([
                'name' => $validated['role'],
            ]);

            // Sincronizar sin perder permisos no administrables.
            $role->syncPermissions($permissions);
        });

        session()->flash(
            'success',
            'Rol actualizado correctamente.'
        );

        return redirect()->route('roles.index');
    }

    /**
     * Renderiza el formulario.
     */
    public function render(): View
    {
        $this->editableRole();

        $permissions = $this->assignablePermissions();

        $selectedIds = array_map(
            'intval',
            $this->permissionIds
        );

        $catalog = app(PermissionCatalog::class);
        $groups = $catalog->groups($permissions);

        return view('livewire.role.edit-role', [
            'permissionGroups' => $groups,
            'groupsForAlpine' => $catalog->forAlpine($groups),
            'permissions' => $permissions,

            'selectedPermissions' => $permissions
                ->filter(
                    fn (Permission $permission): bool => in_array(
                        (int) $permission->id,
                        $selectedIds,
                        true
                    )
                )
                ->values(),
        ]);
    }
}

