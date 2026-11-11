<?php

declare(strict_types=1);

namespace App\Livewire\Pdf;

use App\Jobs\SendPdfEmailJob;
use App\Models\User;
use App\Services\Pdf\PdfGenerator;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class PdfActions extends Component
{
    #[Locked]
    public string $type;

    #[Locked]
    public int $recordId;

    public string $recipient = '';

    public bool $newRecipient = false;

    public bool $showEmailForm = false;

    public string $notice = '';

    public function mount(string $type, int $recordId): void
    {
        $actor = $this->actor();

        app(PdfGenerator::class)->authorize(
            $type,
            $recordId,
            $actor,
            'view'
        );

        $this->type = $type;
        $this->recordId = $recordId;
    }

    public function openEmail(): void
    {
        $pdfs = app(PdfGenerator::class);

        $pdfs->authorize(
            $this->type,
            $this->recordId,
            $this->actor(),
            'email'
        );

        $this->resetValidation();

        $this->notice = '';
        $this->newRecipient = false;

        $this->recipient = $pdfs
            ->document($this->type)
            ->defaultRecipient($this->recordId) ?? '';

        $this->showEmailForm = true;
    }

    public function updatedNewRecipient(bool $checked): void
    {
        abort_unless($this->showEmailForm, 403);

        $pdfs = app(PdfGenerator::class);

        $pdfs->authorize(
            $this->type,
            $this->recordId,
            $this->actor(),
            'email'
        );

        $this->recipient = $checked
            ? ''
            : ($pdfs
                ->document($this->type)
                ->defaultRecipient($this->recordId) ?? '');

        $this->resetValidation('recipient');
    }

    public function closeEmail(): void
    {
        $this->showEmailForm = false;
    }

    public function send(): void
    {
        abort_unless($this->showEmailForm, 403);

        $actor = $this->actor();
        $pdfs = app(PdfGenerator::class);

        $pdfs->authorize(
            $this->type,
            $this->recordId,
            $actor,
            'email'
        );

        if ($this->newRecipient) {
            $this->recipient = trim($this->recipient);
        } else {
            $this->recipient = $pdfs
                ->document($this->type)
                ->defaultRecipient($this->recordId) ?? '';
        }

        $validated = $this->validate([
            'recipient' => [
                'required',
                'email:rfc',
                'max:254',
            ],
        ]);

        $key = 'pdf-email:'.$actor->getAuthIdentifier();

        abort_if(
            RateLimiter::tooManyAttempts($key, 5),
            429,
            'Demasiadas solicitudes de envío.'
        );

        SendPdfEmailJob::dispatch(
            $this->type,
            $this->recordId,
            (int) $actor->getAuthIdentifier(),
            trim($validated['recipient'])
        );

        RateLimiter::hit($key, 60);

        $this->showEmailForm = false;
        $this->notice = 'Solicitud de envío registrada. '
            .'El correo se enviará cuando se procese la cola.';
    }

    public function render(): View
    {
        $actor = $this->actor();
        $pdfs = app(PdfGenerator::class);

        $pdfs->authorize(
            $this->type,
            $this->recordId,
            $actor,
            'view'
        );

        $canEmail = false;

        try {
            $pdfs->authorize(
                $this->type,
                $this->recordId,
                $actor,
                'email'
            );

            $canEmail = true;
        } catch (AuthorizationException) {
            $canEmail = false;
        }

        return view('livewire.pdf.pdf-actions', [
            'canEmail' => $canEmail,
        ]);
    }

    private function actor(): User
    {
        $actor = Auth::guard('web')->user();

        abort_unless($actor instanceof User, 403);

        return $actor;
    }
}
