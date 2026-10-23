<?php

declare(strict_types=1);

namespace App\Pdf\Contracts;

use App\Models\User;

interface PdfDocument
{
    public function authorize(User $actor, int $recordId, string $action): void;

    public function view(): string;

    /** @return array<string, mixed> */
    public function data(int $recordId): array;

    public function fileName(int $recordId): string;

    public function subject(int $recordId): string;

    public function defaultRecipient(int $recordId): ?string;
}
