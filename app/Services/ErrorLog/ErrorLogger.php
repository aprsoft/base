<?php

declare(strict_types=1);

namespace App\Services\ErrorLog;

use App\Models\ErrorLog;
use Illuminate\Support\Facades\Log;
use Throwable;

final class ErrorLogger
{
    /**
     * Persiste una excepción en error_logs.
     *
     * El registro en los canales habituales de Laravel
     * queda a cargo del manejador global de excepciones.
     *
     * No registra información sensible ni interrumpe
     * el flujo cuando falla la persistencia.
     */
    public function persist(
        Throwable $exception,
        array $context = []
    ): void {
        try {
            $file = $exception->getFile();

            $prefix = rtrim(
                base_path(),
                DIRECTORY_SEPARATOR
            ).DIRECTORY_SEPARATOR;

            $file = str_starts_with($file, $prefix)
                ? substr($file, strlen($prefix))
                : basename($file);

            ErrorLog::query()->create([
                'message' => 'Error interno ('.class_basename($exception).')',
                'exception' => $exception::class,
                'file' => $file,
                'line' => (string) $exception->getLine(),
                'errors' => $this->safeContext($context) ?: null,
            ]);

        } catch (Throwable $loggingException) {

            // Un fallo al registrar nunca debe sustituir
            // ni ocultar la excepción original.
            try {
                Log::critical(
                    'No fue posible guardar la excepción en error_logs.',
                    [
                        'original_exception' => $exception::class,
                        'logging_exception' => $loggingException::class,
                    ]
                );
            } catch (Throwable) {
                // Evitar un segundo fallo del sistema de logging.
            }
        }
    }

    /**
     * Permite únicamente metadatos técnicos conocidos.
     * No almacena contraseñas, tokens ni datos arbitrarios.
     */
    private function safeContext(array $context): array
    {
        $safe = [];

        foreach (['operation', 'error_type'] as $key) {
            $value = $context[$key] ?? null;

            if (
                is_string($value)
                && preg_match('/\A[a-zA-Z0-9._-]{1,100}\z/D', $value) === 1
            ) {
                $safe[$key] = $value;
            }
        }

        foreach (['user_id', 'permission_id'] as $key) {
            $value = $context[$key] ?? null;

            if (is_int($value) && $value > 0) {
                $safe[$key] = $value;
            }
        }

        return $safe;
    }
}
