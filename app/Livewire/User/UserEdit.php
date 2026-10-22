<?php

namespace App\Livewire\User;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
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
        $this->authorizeTarget($user);

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

    private function authorizeTarget(User $user): void
    {
        abort_unless(auth('web')->user()?->can('users.edit'), 403);
        abort_if((int) $user->id === 1, 403);
        abort_if($user->trashed(), 404);

        if ($user->hasRole('SuperAdmin', 'web')) {
            abort_unless(auth('web')->user()?->hasRole('SuperAdmin', 'web'), 403);
        }
    }

    private function editableUser(): User
    {
        abort_unless(auth('web')->user()?->can('users.edit'), 403);
        abort_if($this->userId === 1, 403);

        $user = User::query()->findOrFail($this->userId);
        $this->authorizeTarget($user);

        return $user;
    }

    private function assignableRoles(): Collection
    {
        return Role::query()
            ->where('guard_name', 'web')
            ->when(
                ! auth('web')->user()?->hasRole('SuperAdmin', 'web'),
                fn ($query) => $query->where('name', '!=', 'SuperAdmin')
            )
            ->orderBy('name')
            ->get(['id', 'name', 'guard_name']);
    }

    private function validatedData(User $user): array
    {
        $this->name = trim($this->name);
        $this->email = Str::lower(trim($this->email));

        return $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email:rfc',
                'max:255',
                Rule::unique(User::class, 'email')->ignore($user->id),
            ],
            'roleIds' => ['required', 'array', 'min:1'],
            'roleIds.*' => [
                'integer',
                'distinct',
                Rule::in($this->assignableRoles()->modelKeys()),
            ],
        ], [
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

    public function confirmRemoveRole(int $roleId): void
    {
        $this->editableUser();

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
        $this->editableUser();

        if (! $this->assignableRoles()->contains('id', $id)) {
            return;
        }

        $this->roleIds = array_values(array_filter(
            $this->roleIds,
            fn ($roleId) => (int) $roleId !== $id
        ));
    }

    public function confirmSave(): void
    {
        $user = $this->editableUser();
        $validated = $this->validatedData($user);

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if (! $user->isDirty() && ! $this->rolesChanged($user, $validated['roleIds'])) {
            $this->alert('info', 'No hay cambios para guardar.');
            return;
        }

        $this->dispatch(
            'open-confirm-modal',
            id: $user->id,
            name: $user->name,
            confirmEvent: 'user-edit-save-confirmed',
            title: 'Guardar cambios',
            message: '¿Confirmas que deseas actualizar los datos y roles de este usuario?',
            warning: $user->isDirty('email')
                ? 'Cambiar el correo solicitará obligatoriamente un enlace para establecer la contraseña en la nueva dirección.'
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
                $user = User::query()->lockForUpdate()->findOrFail($this->userId);
                $this->authorizeTarget($user);

                $roles = $this->assignableRoles()->whereIn('id', $requestedIds);
                if ($roles->count() !== count($requestedIds)) {
                    throw ValidationException::withMessages([
                        'roleIds' => 'Los roles seleccionados ya no están disponibles.',
                    ]);
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
                $user->syncRoles($roles);

                return [$user, $emailChanged];
            });
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);
            $this->addError('form', 'No fue posible actualizar el usuario. Intenta nuevamente.');
            return;
        }

        if (! $emailChanged) {
            session()->flash('success', 'Usuario y roles actualizados correctamente.');
            $this->redirectRoute('users.index');
            return;
        }

        // El enlace se solicita siempre que cambia el correo.
        // Si falla el transporte, se conserva el cambio y se advierte para permitir reintentar.
        $status = $this->sendResetLink($user);

        if ($status === Password::RESET_LINK_SENT) {
            session()->flash(
                'success',
                'Usuario actualizado. Se solicitó el envío del enlace para establecer la contraseña al nuevo correo.'
            );
            $this->redirectRoute('users.index');
            return;
        }

        session()->flash(
            'warning',
            $status === Password::RESET_THROTTLED
                ? 'Correo actualizado, pero el enlace está limitado por una solicitud reciente. Podrás reenviarlo más tarde.'
                : 'Correo actualizado, pero no se pudo solicitar el enlace de contraseña. Reintenta desde esta pantalla.'
        );
        $this->redirectRoute('users.edit', ['user' => $user->id]);
    }

    public function confirmResetPassword(): void
    {
        $user = $this->editableUser();

        if (Str::lower(trim($this->email)) !== $user->email) {
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

        if (Str::lower(trim($this->email)) !== $user->email) {
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

    private function sendResetLink(User $user): ?string
    {
        try {
            return Password::sendResetLink(['email' => $user->email]);
        } catch (Throwable $exception) {
            report($exception);
            return null;
        }
    }

    private function alert(string $type, string $message): void
    {
        $this->dispatch('app-alert', type: $type, message: $message);
    }

    public function render(): View
    {
        $roles = $this->assignableRoles();
        $selectedIds = array_map('intval', $this->roleIds);

        return view('livewire.user.user-edit', [
            'roles' => $roles,
            'selectedRoles' => $roles->filter(
                fn (Role $role) => in_array((int) $role->id, $selectedIds, true)
            ),
        ]);
    }
}
