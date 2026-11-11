<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

final class DocumentPolicy
{
    public function view(User $actor, Document $document): bool
    {
        return $actor->hasRole('SuperAdmin', 'web')
            || $actor->can('documents.view');
    }

    public function email(User $actor, Document $document): bool
    {
        return $this->view($actor, $document)
            && (
                $actor->hasRole('SuperAdmin', 'web')
                || $actor->can('documents.email')
            );
    }
}
