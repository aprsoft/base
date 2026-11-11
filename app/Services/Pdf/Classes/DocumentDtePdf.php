<?php

declare(strict_types=1);

namespace App\Services\Pdf\Classes;

use App\Models\Document;
use App\Models\User;
use App\Services\Pdf\Classes\Contracts\PdfDocument;
use Illuminate\Support\Facades\Gate;

final class DocumentDtePdf implements PdfDocument
{
    public function authorize(
        User $actor,
        int $recordId,
        string $action
    ): void {
        abort_unless(
            in_array($action, ['view', 'download', 'email'], true),
            404
        );

        $document = Document::query()->findOrFail($recordId);

        Gate::forUser($actor)->authorize('view', $document);

        if ($action === 'email') {
            Gate::forUser($actor)->authorize('email', $document);
        }
    }

    public function view(): string
    {
        return 'pdf.documents.dte';
    }

    public function data(int $recordId): array
    {
        $document = Document::query()
            ->with([
                'customer',
                'supplier',
                'details',
                'typedocument',
            ])
            ->findOrFail($recordId);

        return [
            'document' => $document,

            // Pendiente: obtener el PNG del TED firmado
            // desde el XML del documento correspondiente.
            'stamp' => null,
        ];
    }

    public function fileName(int $recordId): string
    {
        return "dte-{$recordId}.pdf";
    }

    public function defaultRecipient(int $recordId): ?string
    {
        $document = Document::query()
            ->with('customer')
            ->findOrFail($recordId);

        $email = $document->customer?->email_cliente;

        return is_string($email) && trim($email) !== ''
            ? trim($email)
            : null;
    }

    public function subject(int $recordId): string
    {
        return "DTE #{$recordId}";
    }
}
