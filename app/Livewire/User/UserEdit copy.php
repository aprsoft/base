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

    public function mount(User $user): void
    {
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
    }

    /**
     * Actualizar los datos del usuario.
     *
     * Si cambia el correo electrónico, se solicita
     * obligatoriamente un enlace de restablecimiento.
     */
    public function save(): void
    {
        $user = User::findOrFail($this->userId);

        $this->name = trim($this->name);
        $this->email = Str::lower(trim($this->email));

        $validated = $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'required',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                'required',
                Rule::unique(User::class, 'email')
                    ->ignore($user->id),
            ],
        ]);

        $user->fill($validated);

        $emailChanged = $user->isDirty('email');

        if ($emailChanged) {
            // El nuevo correo aún no está verificado.
            $user->email_verified_at = null;
        }

        if (! $user->isDirty()) {
            session()->flash(
                'success',
                'No hay cambios para guardar.'
            );

            return;
        }

        $user->save();

        if (! $emailChanged) {
            session()->flash(
                'success',
                'Usuario actualizado correctamente.'
            );

            return;
        }

        // El correo cambió: solicitar obligatoriamente
        // el enlace para la nueva dirección.
        $status = $this->sendResetLink($user);

        if ($status === Password::RESET_LINK_SENT) {
            session()->flash(
                'success',
                'Correo actualizado correctamente. Se solicitó el envío de un enlace para restablecer la contraseña al nuevo correo electrónico.'
            );

            return;
        }

        session()->flash(
            'warning',
            'El correo fue actualizado, pero no se pudo confirmar el envío del enlace para restablecer la contraseña. Puedes reenviarlo desde esta pantalla.'
        );
    }

    /**
     * Abrir modal de confirmación para enviar
     * un enlace de restablecimiento.
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
            warning: 'Se solicitará el envío de un correo con un enlace seguro.',
            confirmLabel: 'Enviar enlace'
        );
    }

    /**
     * Enviar el enlace después de la confirmación.
     */
    #[On('user-edit-reset-password-confirmed')]
    public function resetPassword(int $id): void
    {
        // Impedir utilizar el evento para cambiar
        // el usuario seleccionado.
        abort_unless($id === $this->userId, 403);

        $user = User::findOrFail($this->userId);

        $status = $this->sendResetLink($user);

        if ($status === Password::RESET_LINK_SENT) {
            session()->flash(
                'success',
                'Se solicitó el envío del enlace para restablecer la contraseña.'
            );

            return;
        }

        if ($status === Password::RESET_THROTTLED) {
            session()->flash(
                'warning',
                'Ya existe una solicitud reciente. Intenta nuevamente más tarde.'
            );

            return;
        }

        session()->flash(
            'warning',
            'No fue posible confirmar el envío del enlace de restablecimiento.'
        );
    }

    /**
     * Enviar enlace al correo actual del usuario.
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

    public function render(): View
    {
        return view('livewire.user.user-edit');
    }
}
