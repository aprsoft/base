<?php

namespace App\Livewire\User;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Spatie\Permission\Models\Role;
use Throwable;

class UserEdit extends Component
{
    #[Locked]
    public int $userId;

    public string $name = '';
    public string $email = '';
    public array $roleIds = [];

    public function mount(User $user): void
    {
        // 'manage' permite acceder a una cuenta suspendida para restaurarla.
        Gate::authorize('manage', $user);

        $this->userId = (int) $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->roleIds = $user->roles
            ->where('guard_name', 'web')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    public function confirmSave(): void
    {
        $user = $this->editableUser();
        $validated = $this->validatedData($user);

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        $isSuperAdminTarget = $user->hasRole('SuperAdmin', 'web');

        if (
            ! $user->isDirty()
            && ($isSuperAdminTarget || ! $this->rolesChanged($user, $validated['roleIds']))
        ) {
            $this->alert('info', 'No hay cambios para guardar.');
            return;
        }

        $this->dispatch(
            'open-confirm-modal',
            id: $user->id,
            name: $user->name,
            confirmEvent: 'user-edit-save-confirmed',
            title: 'Guardar cambios',
            message: $isSuperAdminTarget
                ? '¿Confirmas que deseas actualizar los datos de este usuario? Sus roles no cambiarán.'
                : '¿Confirmas que deseas actualizar los datos y roles de este usuario?',
            warning: $user->isDirty('email')
                ? 'Cambiar el correo solicitará un enlace de contraseña a la nueva dirección.'
                : '',
            confirmLabel: 'Sí, guardar'
        );
    }

    #[On('user-edit-save-confirmed')]
    public function save(int $id): void
    {
        abort_unless($id === $this->userId, 403);

        $current = $this->editableUser();
        $validated = $this->validatedData($current);
        $requestedIds = array_map('intval', $validated['roleIds']);

        try {
            [$user, $emailChanged] = DB::transaction(function () use ($validated, $requestedIds) {
                $user = User::query()
                    ->lockForUpdate()
                    ->findOrFail($this->userId);

                // Reautorizar dentro de la transacción con el registro bloqueado.
                Gate::authorize('update', $user);

                $isSuperAdminTarget = $user->hasRole('SuperAdmin', 'web');

                if (! $isSuperAdminTarget) {
                    $roles = $this->assignableRoles()->whereIn('id', $requestedIds);

                    if ($roles->count() !== count($requestedIds)) {
                        throw ValidationException::withMessages([
                            'roleIds' => 'Los roles seleccionados ya no están disponibles.',
                        ]);
                    }

                    // Protección adicional de la cuenta principal.
                    $this->ensurePrincipalRole($user, $requestedIds);
                }

                $user->fill([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                ]);

                $emailChanged = $user->isDirty('email');

                if ($emailChanged) {
                    $user->email_verified_at = null;
                }

                $user->save();

                if (! $isSuperAdminTarget) {
                    $user->syncRoles($roles);
                }

                return [$user, $emailChanged];
            });
        } catch (ValidationException | AuthorizationException | ModelNotFoundException $exception) {
            // Las validaciones y denegaciones siguen su flujo habitual en Laravel.
            throw $exception;
        } catch (Throwable $exception) {
            $this->reportFailure(
                $exception,
                'No fue posible actualizar el usuario. Intenta nuevamente.'
            );
            return;
        }

        if (! $emailChanged) {
            session()->flash('success', 'Usuario actualizado correctamente.');
            $this->redirectRoute('users.index');
            return;
        }

        // El envío se realiza después del commit de la transacción.
        $status = $this->sendResetLink($user);

        if ($status === Password::RESET_LINK_SENT) {
            session()->flash('success', 'Usuario actualizado. Se solicitó el enlace de contraseña al nuevo correo.');
            $this->redirectRoute('users.index');
            return;
        }

        session()->flash(
            'warning',
            $status === Password::RESET_THROTTLED
                ? 'Correo actualizado, pero el enlace está limitado por una solicitud reciente.'
                : 'Correo actualizado, pero no se pudo solicitar el enlace de contraseña.'
        );
        $this->redirectRoute('users.edit', ['user' => $user->id]);
    }

    public function confirmSuspend(): void
    {
        $user = $this->manageableUser();
        Gate::authorize('suspend', $user);

        $this->dispatch(
            'open-confirm-modal',
            id: $user->id,
            name: $user->name,
            confirmEvent: 'user-edit-suspend-confirmed',
            title: 'Suspender usuario',
            message: '¿Confirmas que deseas suspender el acceso de esta cuenta?',
            warning: 'La cuenta se conserva y podrá restaurarse desde esta ficha.',
            confirmLabel: 'Sí, suspender'
        );
    }

    #[On('user-edit-suspend-confirmed')]
    public function suspend(int $id): void
    {
        abort_unless($id === $this->userId, 403);
        $this->changeSuspension(restore: false);
    }

    public function confirmRestore(): void
    {
        $user = $this->manageableUser();
        Gate::authorize('restore', $user);

        $this->dispatch(
            'open-confirm-modal',
            id: $user->id,
            name: $user->name,
            confirmEvent: 'user-edit-restore-confirmed',
            title: 'Restaurar usuario',
            message: '¿Confirmas que deseas restaurar el acceso de esta cuenta?',
            warning: 'El usuario recuperará el acceso y conservará sus roles anteriores.',
            confirmLabel: 'Sí, restaurar'
        );
    }

    #[On('user-edit-restore-confirmed')]
    public function restore(int $id): void
    {
        abort_unless($id === $this->userId, 403);
        $this->changeSuspension(restore: true);
    }

    /**
     * Cambia el estado con bloqueo y reautorización en la transacción.
     * Los errores de autorización y ausencia del registro se propagan.
     */
    private function changeSuspension(bool $restore): void
    {
        $actor = auth('web')->user();
        abort_unless($actor instanceof User, 403);

        try {
            DB::transaction(function () use ($actor, $restore): void {
                $target = ($restore ? User::withTrashed() : User::query())
                    ->lockForUpdate()
                    ->findOrFail($this->userId);

                Gate::forUser($actor)->authorize($restore ? 'restore' : 'suspend', $target);

                if ($restore) {
                    $target->restore();
                } else {
                    $target->delete(); // SoftDeletes.
                }
            });
        } catch (AuthorizationException | ModelNotFoundException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            $this->reportFailure(
                $exception,
                $restore
                    ? 'No fue posible restaurar al usuario. Intenta nuevamente.'
                    : 'No fue posible suspender al usuario. Intenta nuevamente.'
            );
            return;
        }

        session()->flash(
            'success',
            $restore ? 'Usuario restaurado correctamente.' : 'Usuario suspendido correctamente.'
        );
        $this->redirectRoute('users.index');
    }

    public function confirmResetPassword(): void
    {
        $user = $this->editableUser();

        if ($this->emailDoesNotMatch($user)) {
            $this->addError('email', 'Guarda primero el nuevo correo para enviar el enlace a esa dirección.');
            return;
        }

        $this->dispatch(
            'open-confirm-modal',
            id: $user->id,
            name: $user->name,
            confirmEvent: 'user-edit-reset-password-confirmed',
            title: 'Restablecer contraseña',
            message: '¿Confirmas que deseas enviar un enlace de restablecimiento al correo registrado?',
            warning: 'El enlace tendrá una vigencia de 60 minutos.',
            confirmLabel: 'Sí, enviar enlace'
        );
    }

    #[On('user-edit-reset-password-confirmed')]
    public function resetPassword(int $id): void
    {
        abort_unless($id === $this->userId, 403);

        $user = $this->editableUser();

        if ($this->emailDoesNotMatch($user)) {
            $this->addError('email', 'Guarda primero el correo modificado.');
            return;
        }

        $status = $this->sendResetLink($user);

        if ($status === Password::RESET_LINK_SENT) {
            $this->alert('success', 'Se solicitó el envío del enlace de restablecimiento.');
        } elseif ($status === Password::RESET_THROTTLED) {
            $this->alert('warning', 'Ya existe una solicitud reciente. Intenta más tarde.');
        } else {
            $this->alert('error', 'No fue posible solicitar el enlace de restablecimiento.');
        }
    }

    public function confirmRemoveRole(int $roleId): void
    {
        $user = $this->editableUser();
        abort_if($user->hasRole('SuperAdmin', 'web'), 403);

        $role = $this->assignableRoles()->firstWhere('id', $roleId);

        if (! $role || ! in_array($roleId, array_map('intval', $this->roleIds), true)) {
            return;
        }

        $this->dispatch(
            'open-confirm-modal',
            id: $roleId,
            name: $role->name,
            confirmEvent: 'user-edit-remove-role-confirmed',
            title: 'Quitar rol',
            message: '¿Confirmas que deseas quitar este rol de la selección?',
            warning: 'El cambio se aplicará al guardar el usuario.',
            confirmLabel: 'Sí, quitar'
        );
    }

    #[On('user-edit-remove-role-confirmed')]
    public function removeRole(int $id): void
    {
        $user = $this->editableUser();
        abort_if($user->hasRole('SuperAdmin', 'web'), 403);

        if (! $this->assignableRoles()->contains('id', $id)) {
            return;
        }

        $this->roleIds = array_values(array_filter(
            $this->roleIds,
            fn ($roleId) => (int) $roleId !== $id
        ));
    }

    private function emailDoesNotMatch(User $user): bool
    {
        return Str::lower(trim($this->email)) !== $user->email;
    }

    private function sendResetLink(User $user): ?string
    {
        try {
            return Password::sendResetLink(['email' => $user->email]);
        } catch (Throwable $exception) {
            report($exception);
            return null;
        }
    }

    private function reportFailure(Throwable $exception, string $message): void
    {
        report($exception);
        $this->alert('error', $message);
    }

    private function alert(string $type, string $message): void
    {
        $this->dispatch('app-alert', type: $type, message: $message);
    }

    private function manageableUser(): User
    {
        $user = User::withTrashed()->findOrFail($this->userId);
        Gate::authorize('manage', $user);

        return $user;
    }

    private function editableUser(): User
    {
        $user = $this->manageableUser();
        Gate::authorize('update', $user);

        return $user;
    }

    private function assignableRoles(): Collection
    {
        $actor = auth('web')->user();
        abort_unless($actor instanceof User, 403);

        $roles = Role::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->with('permissions')
            ->get();

        if ($actor->hasRole('SuperAdmin', 'web')) {
            return $roles;
        }

        $allowed = $actor->getAllPermissions()
            ->where('guard_name', 'web')
            ->pluck('name')
            ->all();

        return $roles->filter(
            fn (Role $role) => $role->name !== 'SuperAdmin'
                && $role->permissions->every(
                    fn ($permission) => in_array($permission->name, $allowed, true)
                )
        )->values();
    }

    private function validatedData(User $user): array
    {
        $this->name = trim($this->name);
        $this->email = Str::lower(trim($this->email));

        $isSuperAdminTarget = $user->hasRole('SuperAdmin', 'web');

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email:rfc',
                'max:255',
                Rule::unique(User::class, 'email')->ignore($user->id),
            ],
        ];

        if (! $isSuperAdminTarget) {
            $rules['roleIds'] = ['required', 'array', 'min:1'];
            $rules['roleIds.*'] = [
                'integer',
                'distinct',
                Rule::in($this->assignableRoles()->modelKeys()),
            ];
        }

        $validated = $this->validate($rules, [
            'name.required' => 'Debes ingresar el nombre del usuario.',
            'name.max' => 'El nombre no puede superar los 255 caracteres.',
            'email.required' => 'Debes ingresar el correo electrónico.',
            'email.email' => 'El correo electrónico ingresado no es válido.',
            'email.max' => 'El correo electrónico no puede superar los 255 caracteres.',
            'email.unique' => 'El correo electrónico ingresado ya está registrado.',
            'roleIds.required' => 'Debes seleccionar al menos un rol.',
            'roleIds.min' => 'Debes seleccionar al menos un rol.',
            'roleIds.*.integer' => 'Uno de los roles seleccionados no es válido.',
            'roleIds.*.distinct' => 'No puedes seleccionar un rol más de una vez.',
            'roleIds.*.in' => 'Uno de los roles seleccionados no está permitido.',
        ]);

        if ($isSuperAdminTarget) {
            // Conservar roles existentes de cuentas SuperAdmin.
            $validated['roleIds'] = $user->roles
                ->where('guard_name', 'web')
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
        } else {
            // Defensa adicional para la cuenta principal.
            $this->ensurePrincipalRole($user, $validated['roleIds']);
        }

        return $validated;
    }

    private function ensurePrincipalRole(User $user, array $roleIds): void
    {
        if ((int) $user->getKey() !== 1) {
            return;
        }

        $superAdminRole = Role::query()
            ->where('name', 'SuperAdmin')
            ->where('guard_name', 'web')
            ->first();

        if (
            ! $superAdminRole
            || ! in_array(
                (int) $superAdminRole->getKey(),
                array_map('intval', $roleIds),
                true
            )
        ) {
            throw ValidationException::withMessages([
                'roleIds' => 'La cuenta principal debe conservar el rol SuperAdmin.',
            ]);
        }
    }

    private function rolesChanged(User $user, array $requestedIds): bool
    {
        $current = $user->roles
            ->where('guard_name', 'web')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->sort()
            ->values()
            ->all();

        $requested = array_map('intval', $requestedIds);
        sort($requested);

        return $current !== $requested;
    }

    public function render(): View
    {
        $user = $this->manageableUser();
        $canUpdate = Gate::allows('update', $user);
        $isSuperAdminTarget = $user->hasRole('SuperAdmin', 'web');

        $roles = ($canUpdate && ! $isSuperAdminTarget)
            ? $this->assignableRoles()
            : new Collection();

        $selectedIds = array_map('intval', $this->roleIds);

        return view('livewire.user.user-edit', [
            'user' => $user,
            'isSuspended' => $user->trashed(),
            'canUpdate' => $canUpdate,
            'canSuspend' => Gate::allows('suspend', $user),
            'canRestore' => Gate::allows('restore', $user),
            'isSuperAdminTarget' => $isSuperAdminTarget,
            'roles' => $roles,
            'selectedRoles' => $roles->filter(
                fn (Role $role) => in_array((int) $role->id, $selectedIds, true)
            ),
        ]);
    }
}