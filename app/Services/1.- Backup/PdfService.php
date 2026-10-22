<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PdfService
{
    /**
     * Devuelve una instancia DomPDF ya cargada con la vista.
     */

    
    public function getPdfInstance($pdfData): \Barryvdh\DomPDF\PDF
    {
        $viewPath=$this->viewPath($pdfData->typedocument->name);
    
        return Pdf::loadView($viewPath, ['data' => $pdfData]);
    }      

    /**
     * Guarda el PDF en storage/app/pdf y retorna la ruta completa del archivo.
     */
    public function store($pdfData): string
    {
        $pdf = $this->getPdfInstance($pdfData);

        $filename = Str::of(optional($pdfData->typedocument)->name ?? 'documento')
                        ->lower()
                        ->replaceMatches('/\s+/', '_')
                        ->value() . '_' . now()->timestamp . '.pdf';

        $relativePath = "pdf/{$filename}";
        $fullPath = Storage::path($relativePath);

        // Crear carpeta si no existe
        if (!is_dir(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0777, true);
        }

        file_put_contents($fullPath, $pdf->output());

        return $fullPath;
    }

    /**
     * Respuesta HTTP para visualizar PDF inline (imprimir).
     */
    public function streamInline($pdfData, ?string $filename = null)
    {       
       
        $pdf = $this->getPdfInstance($pdfData);

        $filename = $filename ?? ('documento-' . now()->timestamp . '.pdf');

        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', "inline; filename=\"{$filename}\"");
    }

    /**
     * Respuesta HTTP para forzar descarga del PDF.
     */
    public function download($pdfData, ?string $filename = null)
    {
        $viewPath = $this->viewPath($pdfData->type);
        $pdf = $this->getPdfInstance($viewPath, $pdfData);

        $filename = $filename ?? ('documento-' . now()->timestamp . '.pdf');

        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    public function viewPath($viewPath): string
    {
        return match ($viewPath) {
            'BOLETA EXENTA' => 'tenant.admin.documents.pdf.invoice',
            'BOLETA'  => 'tenant.admin.documents.pdf.invoice',
            'FACTURA'  => 'tenant.admin.documents.pdf.invoice',
            'FACTURA EXENTA'  => 'tenant.admin.documents.pdf.invoice',
            default   => 'tenant.admin.dashboard',
        };
    }   
}
