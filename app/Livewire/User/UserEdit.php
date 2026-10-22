<?php

namespace App\Livewire\User;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Throwable;

class UserEdit extends Component
{
    #[Locked]
    public int $userId;

    public string $name = '';

    public string $email = '';

    /**
     * Inicializar los datos del usuario.
     */
    public function mount(User $user): void
    {
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
    }

    /**
     * Normalizar y validar los datos.
     */
    private function validatedData(User $user): array
    {
        $this->name = trim($this->name);
        $this->email = Str::lower(trim($this->email));

        return $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique(User::class, 'email')
                    ->ignore($user->id),
            ],
        ]);
    }

    /**
     * Validar y abrir el modal de confirmación.
     */
    public function confirmSave(): void
    {
        $user = User::findOrFail($this->userId);

        $validated = $this->validatedData($user);

        $user->fill($validated);

        if (! $user->isDirty()) {
            $this->alert(
                'info',
                'No hay cambios para guardar.'
            );

            return;
        }

        $emailChanged = $user->isDirty('email');

        $this->dispatch(
            'open-confirm-modal',
            id: $user->id,
            name: $user->name,
            confirmEvent: 'user-edit-save-confirmed',
            title: 'Guardar cambios',
            message: '¿Confirmas que deseas guardar los cambios del usuario?',
            warning: $emailChanged
                ? 'Se modificará el correo electrónico y se solicitará un enlace de restablecimiento de contraseña al nuevo correo.'
                : '',
            confirmLabel: 'Sí, guardar'
        );
    }

    /**
     * Guardar después de confirmar.
     */
    #[On('user-edit-save-confirmed')]
    public function save(int $id): void
    {
        abort_unless($id === $this->userId, 403);

        $user = User::findOrFail($this->userId);

        // Volver a validar antes de guardar.
        $validated = $this->validatedData($user);

        $user->fill($validated);

        $emailChanged = $user->isDirty('email');

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        if (! $user->isDirty()) {
            $this->alert(
                'info',
                'No hay cambios para guardar.'
            );

            return;
        }

        try {
            $user->save();
        } catch (Throwable $exception) {
            report($exception);

            $this->alert(
                'error',
                'Ocurrió un error al actualizar el usuario.'
            );

            return;
        }

        // Actualización sin cambio de correo.
        if (! $emailChanged) {
            session()->flash(
                'success',
                'Usuario actualizado correctamente.'
            );

            $this->redirectRoute('users.index');

            return;
        }

        // Cambio de correo: solicitar obligatoriamente
        // el enlace de restablecimiento al nuevo correo.
        $status = $this->sendResetLink($user);

        if ($status === Password::RESET_LINK_SENT) {
            session()->flash(
                'success',
                'Correo actualizado correctamente. Se solicitó el envío de un enlace para restablecer la contraseña.'
            );

            $this->redirectRoute('users.index');

            return;
        }

        $message = $status === Password::RESET_THROTTLED
            ? 'El correo fue actualizado, pero existe una solicitud reciente de restablecimiento. Intenta nuevamente más tarde.'
            : 'El correo fue actualizado, pero no se pudo confirmar el envío del enlace. Puedes solicitarlo nuevamente desde esta pantalla.';

        session()->flash('warning', $message);

        // Permanecer en edición para permitir
        // solicitar otro enlace posteriormente.
        $this->redirectRoute(
            'users.edit',
            ['user' => $user->id]
        );
    }

    /**
     * Confirmación del envío manual del enlace.
     */
    public function confirmResetPassword(): void
    {
        $user = User::findOrFail($this->userId);

        $this->dispatch(
            'open-confirm-modal',
            id: $user->id,
            name: $user->name,
            confirmEvent: 'user-edit-reset-password-confirmed',
            title: 'Restablecer contraseña',
            message: '¿Deseas enviar un enlace para restablecer la contraseña de este usuario?',
            warning: 'Se utilizará el correo electrónico actualmente registrado.',
            confirmLabel: 'Enviar enlace'
        );
    }

    /**
     * Envío manual del enlace.
     */
    #[On('user-edit-reset-password-confirmed')]
    public function resetPassword(int $id): void
    {
        abort_unless($id === $this->userId, 403);

        $user = User::findOrFail($this->userId);

        $status = $this->sendResetLink($user);

        if ($status === Password::RESET_LINK_SENT) {
            $this->alert(
                'success',
                'Se solicitó el envío del enlace para restablecer la contraseña.'
            );

            return;
        }

        if ($status === Password::RESET_THROTTLED) {
            $this->alert(
                'warning',
                'Ya existe una solicitud reciente. Intenta nuevamente más tarde.'
            );

            return;
        }

        $this->alert(
            'error',
            'No fue posible procesar el envío del enlace de restablecimiento.'
        );
    }

    /**
     * Enviar el enlace de contraseña.
     */
    private function sendResetLink(User $user): ?string
    {
        try {
            return Password::sendResetLink([
                'email' => $user->email,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }
    }

    /**
     * Enviar notificación al partial global.
     */
    private function alert(
        string $type,
        string $message
    ): void {
        $this->dispatch(
            'app-alert',
            type: $type,
            message: $message
        );
    }

    public function render(): View
    {
        return view('livewire.user.user-edit');
    }
}
