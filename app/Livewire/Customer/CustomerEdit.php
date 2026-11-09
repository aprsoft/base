<?php

namespace App\Livewire\Customers;

use Livewire\Component;
use App\Models\Customer;
use Usernotnull\Toast\Concerns\WireToast;

class CustomerShow extends Component
{
    use WireToast;

    public ?Customer $customer = null;

    public bool $isCompany;

    public $rut, $nombres, $apellido_paterno, $apellido_materno,
           $giro_cliente, $direccion_cliente, $comuna, $ciudad,
           $telefono_fijo, $telefono_movil, $email_cliente;

    public function mount(?Customer $customer = null)
    {
        $this->customer = $customer;

        if ($customer) {
            $this->rut = $customer->rutFormat;
            $this->nombres = $customer->nombres;
            $this->apellido_paterno = $customer->apellido_paterno;
            $this->apellido_materno = $customer->apellido_materno;
            $this->giro_cliente = $customer->giro_cliente;
            $this->direccion_cliente = $customer->direccion_cliente;
            $this->comuna = $customer->comuna;
            $this->ciudad = $customer->ciudad;
            $this->telefono_fijo = $customer->telefono_fijo;
            $this->telefono_movil = $customer->telefono_movil;
            $this->email_cliente = $customer->email_cliente;
            $this->isCompany = $customer->isCompany();
        }       
    }

    public function update()
    {
     
        try {
            $this->customer->update([
            'nombres' => $this->nombres,
            'apellido_paterno' => $this->apellido_paterno,
            'apellido_materno' => $this->apellido_materno,
            'giro_cliente' => $this->giro_cliente,
            'direccion_cliente' => $this->direccion_cliente,
            'comuna' => $this->comuna,
            'ciudad' => $this->ciudad,
            'telefono_fijo' => $this->telefono_fijo,
            'telefono_movil' => $this->telefono_movil,
            'email_cliente' => $this->email_cliente,
        ]);

        } catch (\Throwable $e) {
            report($e);

            toast()
                ->danger('No se pudo actualizar el cliente')
                ->duration(4000)
                ->push();
        }
        

      

    }

    public function render()
    {
        return view('livewire.customers.customer-show');
    }
}

