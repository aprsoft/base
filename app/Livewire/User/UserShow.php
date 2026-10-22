<?php

declare(strict_types=1);

namespace App\Livewire\User;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class UserShow extends Component
{
    #[Locked]
    public int $userId;

    public function mount(User $user): void
    {
        $this->authorizeView($user);
        $this->userId = (int) $user->getKey();
    }

    private function authorizeView(User $user): void
    {
        // La consulta de usuarios utiliza el permiso users.index existente.
        abort_unless(auth('web')->user()?->can('users.index'), 403);

        // El usuario principal no debe exponerse desde esta pantalla.
        abort_if((int) $user->id === 1, 403);

        if ($user->hasRole('SuperAdmin', 'web')) {
            abort_unless(
                auth('web')->user()?->hasRole('SuperAdmin', 'web'),
                403
            );
        }
    }

    public function render(): View
    {
        abort_unless(auth('web')->user()?->can('users.index'), 403);
        abort_if($this->userId === 1, 403);

        $user = User::withTrashed()
            ->with('roles')
            ->findOrFail($this->userId);

        $this->authorizeView($user);

        return view('livewire.user.user-show', [
            'user' => $user,
            'roles' => $user->roles
                ->where('guard_name', 'web')
                ->sortBy('name'),
        ]);
    }
}
