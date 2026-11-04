<?php

declare(strict_types=1);

namespace App\Services\Pdf;

use App\Models\User;
use App\Pdf\Contracts\PdfDocument;
use App\Pdf\PdfRegistry;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfBuilder;

final class PdfGenerator
{
    public function __construct(private readonly PdfRegistry $registry)
    {
    }

    public function document(string $type): PdfDocument
    {
        return $this->registry->resolve($type);
    }

    public function authorize(string $type, int $recordId, User $actor, string $action): void
    {
        $this->document($type)->authorize($actor, $recordId, $action);
    }

    public function make(string $type, int $recordId, User $actor, string $action): PdfBuilder
    {
        $document = $this->document($type);
        $document->authorize($actor, $recordId, $action);

        return Pdf::view($document->view(), $document->data($recordId))
            ->format('a4')
            ->name($document->fileName($recordId));
    }
}
