<?php

namespace App\Services\Dte\Post\Classes;

use Illuminate\Contracts\Support\Arrayable;

class DteResponse implements Arrayable
{
    public function __construct(
        public ?bool   $accepted = null,  
        public ?int    $document_id = null,    
        public ?int    $folio = null,
        public ?string $message = null,
        public ?array   $warnings = [],
        public ?array   $errors = [],
       
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
        ], fn($value) => $value !== null); // elimina solo los null
    }
}
