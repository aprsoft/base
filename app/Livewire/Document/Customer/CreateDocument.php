<?php

namespace App\Livewire\Document\Customer;

use App\Models\Customer;
use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class CreateDocument extends Component
{
    public bool $clienteGenerico = true;

    public string $busquedaServicio = '';

    public string $busquedaCliente = '';

    #[Locked]
    public ?int $clienteId = null;

    #[Computed]
    public function servicios(): Collection
    {
        $busqueda = trim($this->busquedaServicio);

        return Service::query()
            ->select(['id', 'name', 'valor', 'tipo_servicio'])
            ->where('estado_servicio', 'VIGENTE')
            ->when($busqueda !== '', function (Builder $query) use ($busqueda): void {
                $query->where(function (Builder $q) use ($busqueda): void {
                    $q->where('name', 'like', '%' . $busqueda . '%');

                    if (ctype_digit($busqueda)) {
                        $q->orWhere('id', (int) $busqueda);
                    }
                });
            })
            ->orderBy('name')
            ->limit(40)
            ->get();
    }

    #[Computed]
    public function clientes(): Collection
    {
        $busqueda = trim($this->busquedaCliente);

        if ($this->clienteGenerico || mb_strlen($busqueda) < 2) {
            return new Collection();
        }

        return Customer::query()
            ->select([
                'id', 'rut', 'nombres', 'apellido_paterno', 'apellido_materno',
                'condicion_cliente', 'giro', 'direccion_cliente', 'comuna',
            ])
            ->where(function (Builder $query) use ($busqueda): void {
                $query->where('rut', 'like', '%' . $busqueda . '%')
                    ->orWhereRaw(
                        "CONCAT_WS(' ', nombres, apellido_paterno, apellido_materno) LIKE ?",
                        ['%' . $busqueda . '%']
                    );
            })
            ->orderBy('nombres')
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function clienteSeleccionado(): ?Customer
    {
        if ($this->clienteGenerico || $this->clienteId === null) {
            return null;
        }

        return Customer::query()
            ->select([
                'id', 'rut', 'nombres', 'apellido_paterno', 'apellido_materno',
                'condicion_cliente', 'giro', 'direccion_cliente', 'comuna',
            ])
            ->find($this->clienteId);
    }

    public function seleccionarCliente(int $id): void
    {
        if ($this->clienteGenerico) {
            return;
        }

        $cliente = Customer::query()->findOrFail($id);
        $this->clienteId = $cliente->id;
        $this->busquedaCliente = '';
    }

    public function limpiarCliente(): void
    {
        $this->clienteId = null;
        $this->busquedaCliente = '';
    }

    public function updatedClienteGenerico(): void
    {
        if ($this->clienteGenerico) {
            $this->limpiarCliente();
        }
    }

    public function render()
    {
        return view('livewire.document.customer.create-document');
    }
}