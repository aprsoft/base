<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\GenericMail;
use App\Models\User;
use App\Services\Pdf\PdfGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

final class SendPdfEmailJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    // Impide reintentos automáticos de un correo cuyo estado de entrega es incierto.
    public int $tries = 1;

    public int $timeout = 75;

    public function __construct(
        public readonly string $type,
        public readonly int $recordId,
        public readonly int $actorId,
        public readonly string $recipient,
    ) {
    }

    public function handle(PdfGenerator $pdfs): void
    {
        // El solicitante puede haber perdido permisos mientras esperaba la cola.
        $actor = User::query()->findOrFail($this->actorId);

        $pdf = $pdfs->make($this->type, $this->recordId, $actor, 'email');
        $document = $pdfs->document($this->type);
        $content = $pdf->generatePdfContent();

        // El PDF viaja como adjunto al enviar; nunca como carga binaria del Job.
        Mail::to($this->recipient)->send(new GenericMail(
            subjectLine: $document->subject($this->recordId),
            viewName: 'mail.pdf-document',
            data: ['documentName' => $document->subject($this->recordId)],
            customAttachments: [[
                'data' => $content,
                'options' => [
                    'as' => $document->fileName($this->recordId),
                    'mime' => 'application/pdf',
                ],
            ]],
        ));
    }
}
