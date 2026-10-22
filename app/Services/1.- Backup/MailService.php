<?php

namespace App\Services;

use App\Jobs\SendEmailJob;

class MailService
{
    public function sendMail($data, ?string $customAttachments = null): void
    {
        $payload = [
            'to' => $data->email,
            'subjectLine' => 'Documento generado',
            'viewName' => $this->viewPath($data->typedocument->name),
            'data' => $data,
            'customAttachments' => [
                [
                    'path' => $customAttachments,
                    'options' => ['as' => 'documento.pdf']
                ]
            ]
        ];       

        SendEmailJob::dispatch($payload);
    }

    private function viewPath(string $viewPath): string
    {
        return match ($viewPath) {           
            'FACTURA'   => 'tenant.admin.documents.mail.invoice',
            'FACTURA EXENTA'   => 'tenant.admin.documents.mail.invoice',
            'BOLETA'   => 'tenant.admin.documents.mail.invoice',
            'BOLETA EXENTA'   => 'tenant.admin.documents.mail.invoice',
            'VOUCHER'   => 'tenant.admin.documents.mail.voucher',
            'SENIORITY' => 'tenant.admin.documents.mail.seniority',
            'ENDOWMENT' => 'tenant.admin.documents.mail.endowment',
            default     => throw new \Exception("Tipo de correo no válido: $viewPath"),
        };
    }
}
