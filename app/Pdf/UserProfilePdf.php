<?php

declare(strict_types=1);

namespace App\Pdf;

use App\Models\User;
use App\Pdf\Contracts\PdfDocument;

final class UserProfilePdf implements PdfDocument
{
    public function authorize(User $actor, int $recordId, string $action): void
    {
        abort_unless(in_array($action, ['view', 'download', 'email'], true), 404);
        abort_unless($actor->can('users.index'), 403);
        abort_if($recordId === 1, 403);

        // Permiso adicional para enviar datos personales fuera de Base.
        if ($action === 'email') {
            abort_unless($actor->can('users.edit'), 403);
        }

        $target = User::withTrashed()->findOrFail($recordId);

        if ($target->hasRole('SuperAdmin', 'web')) {
            abort_unless($actor->hasRole('SuperAdmin', 'web'), 403);
        }
    }

    public function view(): string
    {
        return 'pdf.users.profile';
    }

    public function data(int $recordId): array
    {
        // Solo debe invocarse después de authorize() desde PdfGenerator.
        $user = User::withTrashed()->with('roles')->findOrFail($recordId);

        return [
            'user' => $user,
            'roles' => $user->roles
                ->where('guard_name', 'web')
                ->sortBy('name'),
        ];
    }

    public function fileName(int $recordId): string
    {
        return "usuario-{$recordId}.pdf";
    }

    public function subject(int $recordId): string
    {
        return "Perfil del usuario #{$recordId}";
    }
}
