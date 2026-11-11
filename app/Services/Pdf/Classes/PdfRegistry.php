<?php

declare(strict_types=1);

namespace App\Services\Pdf\Classes;

use App\Services\Pdf\Classes\Contracts\PdfDocument;

final class PdfRegistry
{
    public function resolve(string $type): PdfDocument
    {
        // Nunca resolver clases o vistas arbitrarias desde una petición HTTP.
        return match ($type) {
            'user.profile' => app(UserProfilePdf::class),
            default => abort(404),
        };
    }
}

