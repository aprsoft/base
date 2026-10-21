<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class GenericMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $subjectLine;
    public string $view;
    public object $document;
    public array $attachments;

    public function __construct(
        string $subjectLine,
        string $view,
        object $document,
        array $attachments = []
    ) {
        $this->subjectLine = $subjectLine;
        $this->view = $view;
        $this->document = $document;
        $this->attachments = $attachments;
    }

    /**
     * Define metadata del correo (asunto, reply-to, from, tags, etc.)
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine,
        );
    }

    /**
     * Define el contenido del correo (vista, markdown, datos)
     */
    public function content(): Content
    {
        return new Content(
            view: $this->view,
            with: [
                'document' => $this->document,
            ],
        );
    }

    /**
     * Define los adjuntos
     */
    public function attachments(): array
    {
        return array_map(function ($file) {
            return Attachment::fromPath($file['path'])
                ->as($file['options']['as'] ?? null)
                ->withMime($file['options']['mime'] ?? null);
        }, $this->attachments);
    }
}
