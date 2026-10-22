<?php

declare(strict_types=1);

namespace App\Services\ErrorLog;

use App\Models\ErrorLog;
use Illuminate\Support\Facades\Log;
use Throwable;

class ErrorLogger
{
    /**
     * Registra una excepción en el log de Laravel y,
     * cuando sea posible, en la tabla central error_logs.
     *
     * Si la excepción se produce dentro del contexto de un tenant,
     * agrega automáticamente su identificador al contexto del error.
     */
    public function report(Throwable $exception, array $context = []): void
    {      


        Log::error($exception->getMessage(), [
            'exception' => $exception,
            'context' => $context,
        ]);

        try {
          
            $errorLog = new ErrorLog();           

            $errorLog->fill([
               
                'message' => $exception->getMessage(),
                'exception' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'errors' => $context ?: null,
            ]);

            $errorLog->save();
        } catch (Throwable $loggingException) {
            /*
             * Un fallo del propio sistema de logging nunca debe
             * reemplazar ni ocultar la excepción original.
             */
            Log::critical(
                'Falló la persistencia de una excepción en error_logs.',
                [
                    'original_exception' => get_class($exception),
                    'original_message' => $exception->getMessage(),
                    'logging_exception' => get_class($loggingException),
                    'logging_message' => $loggingException->getMessage(),
                    'context' => $context,
                ]
            );
        }
    }
}