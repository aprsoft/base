<?php

declare(strict_types=1);

namespace App\Services\Central\ErrorLog;

use App\Models\Central\ErrorLog;
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

    
        /*
         * Si existe un tenant inicializado, registramos automáticamente
         * su identificador. De esta forma los callers no necesitan
         * agregar tenant_id manualmente.
         */
        $tenantId = tenant()?->getTenantKey();


        // if ($tenantId !== null) {
        //     $context['tenant_id'] = $tenantId;
        // }

        /*
         * Laravel log.
         */
        Log::error($exception->getMessage(), [
            'exception' => $exception,
            'context' => $context,
        ]);

        try {
            /*
             * La persistencia de errores siempre debe realizarse
             * utilizando la conexión CENTRAL.
             */
            $centralConnection = config(
                'tenancy.database.central_connection'
            );

            if (
                ! is_string($centralConnection)
                || $centralConnection === ''
            ) {
                Log::warning(
                    'No fue posible persistir la excepción en error_logs: conexión central no configurada.',
                    [
                        'exception_class' => get_class($exception),
                        'context' => $context,
                    ]
                );

                return;
            }

            $errorLog = new ErrorLog();

            /*
             * ErrorLog pertenece exclusivamente al contexto central.
             *
             * Nunca debe utilizar accidentalmente la conexión activa
             * del tenant.
             */
            $errorLog->setConnection($centralConnection);

            $errorLog->fill([
                'tenant_id' => $tenantId,
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