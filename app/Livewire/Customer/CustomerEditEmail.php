<?php

namespace App\Livewire\Customers;

use App\Models\Meter;
use App\Models\Customer;
use Livewire\Component;

class CustomerEditEmail extends Component
{
    public bool      $show  = false;
    public string    $emailClienteModal;
    public ?Customer $customer = null;
    public ?Meter    $meter = null;

    public function mount(): void
    {       
        $this->customer = $this->customer
            ?? $this->meter?->customer?->first();

        $this->emailClienteModal = $this->customer?->email_cliente ?? '';
    }


    protected $rules = [
        'emailClienteModal' => 'required|email:dns',
    ];

    public function open(): void
    {
        // $this->email = $currentEmail;
        $this->show = true;
    }

    public function save(): void
    {
        // dd($this->emailClienteModal);
        $this->validateOnly('emailClienteModal');
        $this->customer->update(['email_cliente'=>$this->emailClienteModal]);
        $this->dispatch('customer-email-updated');
        $this->show = false;        
    }

    public function cancel(): void
    {
        $this->resetValidation();
        $this->show = false;
    }

    public function render()
    {
        return view('livewire.customers.customer-edit-email');
    }
}
