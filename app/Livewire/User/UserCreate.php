<?php

namespace App\Livewire\User;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class UserCreate extends Component
{
    public string $name = '';

    public string $email = '';

    public function mount(): void
    {
        // Gate::authorize('create', User::class);
    }

    public function save(): void
    {
        // Gate::authorize('create', User::class);

        $this->name = trim($this->name);
        $this->email = Str::lower(trim($this->email));

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique(User::class, 'email'),
            ],
        ]);

        // Contraseña temporal aleatoria.
        // El modelo User aplica el cast "hashed".
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Str::random(64),
        ]);

        // El usuario establece su propia contraseña
        // mediante el mecanismo nativo de Laravel.
        try {
            $status = Password::sendResetLink([
                'email' => $user->email,
            ]);
         
        } catch (\Throwable $exception) {

      
            report($exception);
            $status = null;
        }

        $this->reset('name', 'email');

        if ($status === Password::RESET_LINK_SENT) {
            session()->flash(
                'success',
                'Usuario creado correctamente. Se ha solicitado el envío del enlace para establecer su contraseña.'
            );

            return;
        }

        session()->flash(
            'warning',
            'El usuario fue creado, pero no se pudo confirmar el envío del correo. Puede solicitar otro enlace desde la recuperación de contraseña.'
        );
    }

    public function render(): View
    {
        return view('livewire.user.user-create');
    }
}
