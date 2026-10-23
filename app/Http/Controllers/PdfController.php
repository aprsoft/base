<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Pdf\PdfGenerator;
use Illuminate\Support\Facades\Auth;

final class PdfController extends Controller
{
    public function show(string $type, int $record, PdfGenerator $pdfs)
    {
        $actor = Auth::guard('web')->user();
        abort_unless($actor, 403);

        $pdf = $pdfs->make($type, $record, $actor, 'view');

        return $pdf->inline($pdfs->document($type)->fileName($record));
    }

    public function download(string $type, int $record, PdfGenerator $pdfs)
    {
        $actor = Auth::guard('web')->user();
        abort_unless($actor, 403);

        return $pdfs->make($type, $record, $actor, 'download')->download();
    }
}
