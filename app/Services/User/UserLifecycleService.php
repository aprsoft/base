<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class UserLifecycleService
{
    public function suspend(User $actor, int $targetId): void
    {
        DB::transaction(function () use ($actor, $targetId): void {
            $target = User::query()->lockForUpdate()->findOrFail($targetId);
            Gate::forUser($actor)->authorize('suspend', $target);

            // Suspensión lógica: conservar usuario, roles y relaciones.
            $target->delete();
        });
    }

    public function restore(User $actor, int $targetId): void
    {
        DB::transaction(function () use ($actor, $targetId): void {
            $target = User::withTrashed()->lockForUpdate()->findOrFail($targetId);
            Gate::forUser($actor)->authorize('restore', $target);

            $target->restore();
        });
    }
}
