<?php

declare(strict_types=1);

namespace App\Pdf;

use App\Models\User;
use App\Pdf\Contracts\PdfDocument;
use Illuminate\Support\Facades\Gate;

final class UserProfilePdf implements PdfDocument
{
    public function authorize(User $actor, int $recordId, string $action): void
    {
        abort_unless(in_array($action, ['view', 'download', 'email'], true), 404);
        $target = User::withTrashed()->findOrFail($recordId);

        Gate::forUser($actor)->authorize('view', $target);

        if ($action === 'email') {
            Gate::forUser($actor)->authorize('update', $target);
        }
    }

    public function view(): string
    {
        return 'pdf.users.profile';
    }

    public function data(int $recordId): array
    {
        $user = User::withTrashed()->with('roles')->findOrFail($recordId);

        return [
            'user' => $user,
            'roles' => $user->roles->where('guard_name', 'web')->sortBy('name'),
        ];
    }

    public function fileName(int $recordId): string
    {
        return "usuario-{$recordId}.pdf";
    }

    public function defaultRecipient(int $recordId): ?string
    {
        return User::withTrashed()->findOrFail($recordId)->email;
    }

    public function subject(int $recordId): string
    {
        return "Perfil del usuario #{$recordId}";
    }
}
