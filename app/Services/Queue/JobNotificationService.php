<?php

namespace App\Services\Queue;

use App\Events\JobNotification;
use App\Jobs\SendEmailJob;
use App\Services\Central\ErrorLog\ErrorLogger;
use Throwable;

class JobNotificationService
{
    /**
     * Jobs que deben informar el resultado al usuario.
     *
     * Los Jobs que no estén aquí igualmente serán supervisados:
     * - si fallan, JobFailureHandler los registra;
     * - si tienen éxito, simplemente no generan mensaje.
     */
    private const NOTIFICATIONS = [
        SendEmailJob::class => [
            'success' => 'Correo enviado correctamente.',
            'error' => 'No fue posible enviar el correo.',
        ],

        /*
         * Ejemplo futuro:
         *
         * GeneratePdfJob::class => [
         *     'success' => 'PDF generado correctamente.',
         *     'error' => 'No fue posible generar el PDF.',
         * ],
         */
    ];

    public function __construct(
        private readonly ErrorLogger $errorLogger
    ) {
    }

    public function notifySuccess(
        string $jobClass,
        array $payload
    ): void {
        $this->notify(
            jobClass: $jobClass,
            type: 'success',
            payload: $payload
        );
    }

    public function notifyFailure(
        string $jobClass,
        array $payload
    ): void {
        $this->notify(
            jobClass: $jobClass,
            type: 'error',
            payload: $payload
        );
    }

    private function notify(
        string $jobClass,
        string $type,
        array $payload
    ): void {
        $message = self::NOTIFICATIONS[$jobClass][$type] ?? null;

        /*
         * Job supervisado pero no notificable.
         */
        if (! is_string($message) || $message === '') {
            return;
        }

        $context = $payload['ssr_notification'] ?? null;

        /*
         * El Job puede haber sido iniciado por cron, Artisan,
         * otro Job u otro proceso sin usuario autenticado.
         */
        if (! is_array($context)) {
            return;
        }

        $scope = $context['scope'] ?? null;
        $tenantId = $context['tenant_id'] ?? null;
        $userId = $context['user_id'] ?? null;

        if (
            ! in_array($scope, ['central', 'tenant'], true)
            || ! is_numeric($userId)
        ) {
            return;
        }

        if (
            $scope === 'tenant'
            && (
                ! is_string($tenantId)
                || $tenantId === ''
            )
        ) {
            return;
        }

        if ($scope === 'central') {
            $tenantId = null;
        }

        try {
            event(
                new JobNotification(
                    scope: $scope,
                    userId: (int) $userId,
                    tenantId: $tenantId,
                    type: $type,
                    message: $message
                )
            );
        } catch (Throwable $exception) {
            /*
             * El Job ya terminó.
             *
             * Una falla de Reverb no debe convertir un Job exitoso
             * en un Job fallido.
             */
            $this->errorLogger->report(
                $exception,
                [
                    'operation' => 'queue.notification.broadcast',
                    'error_type' => 'broadcast',
                    'job' => $jobClass,
                    'scope' => $scope,
                    'tenant_id' => $tenantId,
                    'user_id' => (int) $userId,
                    'notification_type' => $type,
                ]
            );
        }
    }
}