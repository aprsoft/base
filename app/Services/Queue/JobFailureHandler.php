<?php

namespace App\Services\Queue;

use App\Services\Central\ErrorLog\ErrorLogger;
use Illuminate\Queue\Events\JobFailed;

class JobFailureHandler
{
    public function __construct(
        private readonly ErrorLogger $errorLogger,
        private readonly JobNotificationService $notificationService
    ) {
    }

    public function handle(JobFailed $event): void
    {
        $payload = $event->job->payload();

        $notification = $payload['ssr_notification'] ?? [];

        /*
         * Todos los Jobs fallidos se registran,
         * independientemente de si notifican o no al usuario.
         */
        $this->errorLogger->report(
            $event->exception,
            [
                'operation' => 'queue.job.failed',
                'error_type' => 'queue',

                'connection' => $event->connectionName,

                'job' => $event->job->resolveName(),
                'job_id' => $event->job->getJobId(),
                'job_uuid' => $event->job->uuid(),

                'scope' => $notification['scope'] ?? null,
                'tenant_id' => $notification['tenant_id'] ?? null,
                'user_id' => $notification['user_id'] ?? null,
            ]
        );

        /*
         * Solo los Jobs configurados como notificables
         * mostrarán mensaje de error al usuario.
         */
        $this->notificationService->notifyFailure(
            jobClass: $event->job->resolveName(),
            payload: $payload
        );
    }
}