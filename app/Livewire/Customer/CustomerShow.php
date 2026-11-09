<?php

namespace App\Livewire\Customers;

use Livewire\Component;
use App\Models\Customer;
use Usernotnull\Toast\Concerns\WireToast;

class CustomerShow extends Component
{
    use WireToast;

    public ?Customer $customer = null;

    public bool $isCompany = false;

    public ?string $rut = null;
    public ?string $nombres = null;
    public ?string $apellidoPaterno = null;
    public ?string $apellidoMaterno = null;
    public ?string $giroCliente = null;
    public ?string $direccionCliente = null;
    public ?string $telefonoFijo = null;
    public ?string $telefonoMovil = null;
    public ?string $emailCliente = null;

    public function mount(?Customer $customer = null): void
    {
        $this->customer = $customer;

        if (!$customer) {
            return;
        }

        $this->rut              = $customer->rutFormat;
        $this->nombres          = $customer->nombres;
        $this->apellidoPaterno  = $customer->apellido_paterno;
        $this->apellidoMaterno  = $customer->apellido_materno;
        $this->giroCliente      = $customer->giro_cliente;
        $this->direccionCliente = $customer->direccion_cliente;
        $this->telefonoFijo     = $customer->telefono_fijo;
        $this->telefonoMovil    = $customer->telefono_movil;
        $this->emailCliente     = $customer->email_cliente;
        $this->isCompany        = $customer->isCompany();
    }

    public function render()
    {
        return view('livewire.customers.customer-show');
    }
}