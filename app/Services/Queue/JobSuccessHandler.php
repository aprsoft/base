<?php

namespace App\Services\Queue;

use Illuminate\Queue\Events\JobProcessed;

class JobSuccessHandler
{
    public function __construct(
        private readonly JobNotificationService $notificationService
    ) {
    }

    public function handle(JobProcessed $event): void
    {
       
        $this->notificationService->notifySuccess(
            jobClass: $event->job->resolveName(),
            payload: $event->job->payload()
        );
    }
}