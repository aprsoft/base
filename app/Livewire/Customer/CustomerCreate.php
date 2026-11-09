<?php

namespace App\Livewire\Customers;

use Livewire\Component;
use App\Models\Customer;
use App\Models\ErrorLog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CustomerCreate extends Component
{
    public bool $isCompany = false;

    // Campos comunes
    public string  $rut = '';
    public string  $nombres = '';
    public ?string $direccion_cliente = null;
    public ?string $email_cliente = null;
    public ?string $telefono_fijo = null;
    public ?string $telefono_movil = null;
    public ?string $apellido_paterno = null;
    public ?string $apellido_materno = null;
    public ?string $giro = null;
    public Collection $cliente_location; 

    public function mount(): void
    {
        $this->cliente_location = collect([
           'region_id'   => tenant('region_id'),
           'province_id' => tenant('province_id'),
           'commune_id'  => tenant('commune_id'),
           'locality_id' => tenant('locality_id'),
        ]);          
    } 

    protected function rules(): array
    {
        return $this->isCompany
            ? [
                'rut'                => ['required'],
                'nombres'            => ['required', 'string'],
                'giro'               => ['required', 'string'],
                'direccion_cliente'  => ['required', 'string'],
            ]
            : [
                'rut'                => ['required'],
                'nombres'            => ['required', 'string'],
                'apellido_paterno'   => ['required', 'string'],
                'apellido_materno'   => ['required', 'string'],
                'direccion_cliente'  => ['required', 'string'],
            ];
    }

    public function save(): void
    {

    try {

            $this->validate();

        } catch (ValidationException $ve) {

             ErrorLog::create([
                'message'   => $ve->getMessage(),
                'exception' => get_class($ve),
                'file'      => $ve->getFile(),
                'line'      => $ve->getLine(),
                'errors'    => $ve->errors(),
            ]);

            toast()
                ->danger('Errores de validación detectados')
                ->sticky()
                ->duration(5000)
                ->push();

            // Agregar errores al componente Livewire para que se muestren en la UI
            foreach ($ve->errors() as $field => $messages) {
                foreach ($messages as $message) {
                    $this->addError($field, $message);
                }
            }

            return; // detener ejecución
        }

        try {

            DB::beginTransaction();

            Customer::create([
                'rut'                => $this->rut,
                'nombres'            => $this->nombres,
                'apellido_paterno'   => $this->isCompany ? null : $this->apellido_paterno,
                'apellido_materno'   => $this->isCompany ? null : $this->apellido_materno,
                'giro'               => $this->isCompany ? $this->giro : null,
                'direccion_cliente'  => $this->direccion_cliente,
                'email'              => $this->email,
                'telefono_fijo'      => $this->movil,
                'telefono_movil'     => $this->movil,
                'condicion_cliente'  => $this->isCompany ? 'EMPRESA' : 'PARTICULAR',
                'tipo_cliente'       => request()->routeIs('customers.create')
                    ? 'CLIENTE'
                    : 'SOCIO',
            ]);

            $this->redirectRoute('customers.index');

        } catch (\Throwable $e) {

            DB::rollBack();

             ErrorLog::create([
                'message'   => $e->getMessage(),
                'exception' => get_class($e),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),                
            ]);
        

            toast()
                ->danger('Error técnico al guardar el contrato')
                ->sticky()
                ->push();

            $this->addError('general', 'Error al guardar el contrato.');

            return; // importante para detener ejecución y no lanzar 500     

        }
       

        Customer::create([
            'rut'                => $this->rut,
            'nombres'            => $this->nombres,
            'apellido_paterno'   => $this->isCompany ? null : $this->apellido_paterno,
            'apellido_materno'   => $this->isCompany ? null : $this->apellido_materno,
            'giro'               => $this->isCompany ? $this->giro : null,
            'direccion_cliente'  => $this->direccion_cliente,
            'email'              => $this->email,
            'telefono_fijo'      => $this->movil,
            'telefono_movil'     => $this->movil,
            'condicion_cliente'  => $this->isCompany ? 'EMPRESA' : 'PARTICULAR',
            'tipo_cliente'       => request()->routeIs('customers.create')
                ? 'CLIENTE'
                : 'SOCIO',
        ]);

        $this->redirectRoute('customers.index');
    }

    public function render()
    {
        return view('livewire.customers.customer-create');
    }
}
