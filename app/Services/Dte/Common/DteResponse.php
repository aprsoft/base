<?php

namespace App\Services\Dte;

use Illuminate\Contracts\Support\Arrayable;

class DteResponse implements Arrayable
{
    public function __construct(
        public ?bool   $accepted = null,
        public ?int    $document_id = null,
        public ?int    $folio = null,
        public ?string $message = null,
        public array   $warnings = [],
        public array   $errors = [],
        public ?string $statusCode = null,
        public ?int    $current_page = null,
        public ?int    $last_page = null,
        public array   $data = [],
        public ?int    $total = null,
        public ?string $pdf = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'accepted'     => $this->accepted,
            'document_id'  => $this->document_id,
            'folio'        => $this->folio,
            'message'      => $this->message,
            'warnings'     => $this->warnings,
            'errors'       => $this->errors,
            'statusCode'   => $this->statusCode,
            'current_page' => $this->current_page,
            'last_page'    => $this->last_page,
            'data'         => $this->data,
            'total'        => $this->total,
        ], fn($value) => $value !== null); // elimina solo los null
    }
}
