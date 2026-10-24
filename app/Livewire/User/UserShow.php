<?php

declare(strict_types=1);

namespace App\Livewire\User;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;

class UserShow extends Component
{
    #[Locked]
    public int $userId;

    public function mount(User $user): void
    {
        Gate::authorize('view', $user);
        $this->userId = (int) $user->getKey();
    }

    public function render(): View
    {
        $user = User::withTrashed()->with('roles')->findOrFail($this->userId);
        Gate::authorize('view', $user);

        return view('livewire.user.user-show', [
            'user' => $user,
            'isSuperAdminTarget' => $user->hasRole('SuperAdmin', 'web'),
            'roles' => $user->roles->where('guard_name', 'web')->sortBy('name'),
        ]);
    }
}
