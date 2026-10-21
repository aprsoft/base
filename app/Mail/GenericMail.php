<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Support\Facades\Storage;

class GenericMail extends Mailable
{
    public function __construct(
        public string $subjectLine,
        public string $viewName,
        public mixed $data,
        protected array $customAttachments = []
    ) {}

    /**
     * Asunto del correo
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine,
        );
    }

    /**
     * Vista y datos del correo
     */
    public function content(): Content
    {
        return new Content(
            view: $this->viewName,
            with: [
                'data' => $this->data,
            ]
        );
    }

    /**
     * Attachments del correo
     */
    public function attachments(): array
    {
        $attachments = [];

        foreach ($this->customAttachments as $att) {

            // Adjuntar desde archivo
            if (!empty($att['path'])) {
                $path = $att['path'];

                if (!file_exists($path)) {
                    $path = Storage::path($att['path']);
                    if (!file_exists($path)) {
                        continue;
                    }
                }

                $attachments[] = Attachment::fromPath($path)
                    ->as($att['options']['as'] ?? basename($path))
                    ->withMime($att['options']['mime'] ?? null);
            }

            // Adjuntar desde contenido en memoria
            if (!empty($att['data'])) {
                $attachments[] = Attachment::fromData(
                    fn () => $att['data'],
                    $att['options']['as'] ?? 'attachment.pdf'
                )->withMime(
                    $att['options']['mime'] ?? 'application/pdf'
                );
            }
        }

        return $attachments;
    }
}
