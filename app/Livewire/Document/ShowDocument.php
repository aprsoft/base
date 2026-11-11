<?php

declare(strict_types=1);

namespace App\Livewire\Document;

use App\Models\Document;
use App\Services\Dte\Get\DteGetManager;
use App\Services\Dte\Stamp\TimbrePdf417Service;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ShowDocument extends Component
{
    public Document $document;

    public function mount(Document $document): void
    {
       $this->document = $document;
        
    }


    public function render(): View
    {       

        return view('livewire.document.show-document',);
    }
}
