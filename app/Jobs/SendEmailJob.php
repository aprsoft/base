<?php

namespace App\Jobs;

use App\Mail\GenericMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public array $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function handle(): void
    {
        Mail::to($this->payload['to'])
            ->send(
                new GenericMail(
                    subjectLine: $this->payload['subjectLine'],
                    viewName: $this->payload['viewName'],
                    data: $this->payload['data'],
                    customAttachments:
                        $this->payload['customAttachments'] ?? []
                )
            );
    }
}