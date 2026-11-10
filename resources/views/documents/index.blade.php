@extends('layouts.app')

@section('content')

    <x-common.page-breadcrumb :pageTitle="'Documentos'" />
    
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">

            {{-- Bloque izquierdo: Título + subtítulo --}}
            <div>
                @php
                    $title = '';
                    $subtitle = null;

                    if (isset($customer)) {
                        $title = '{{ $customer->fullName }}';
                        $subtitle = 'Historial completo de documentos asociados al cliente.';
                    } elseif ($entity === 'customers' && ! isset($customer)) {
                        $title = 'Listado de Documentos de Clientes';
                        $subtitle = 'Historial completo de documentos asociados a los clientes.';
                    } elseif (isset($supplier)) {
                        $title = 'Documentos del Proveedor';
                        $subtitle = 'Historial completo de documentos asociados al proveedor.';
                    } elseif ($entity === 'suppliers') {
                        $title = 'Listado de Documentos de Proveedores';
                    } elseif (isset($meter)) {
                        $title = 'Documentos del Medidor';
                        $subtitle = 'Documentos asociados al medidor seleccionado.';
                    } elseif ($entity === 'overdue') {
                        $title = 'Listado de Clientes Morosos';
                        $subtitle = 'Listado de clientes y documentos morosos.';
                    } 
                @endphp

                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 leading-tight">
                    <span class="text-lg font-semibold text-indigo-600 dark:text-indigo-400">
                        {{ $customer->fullName }}
                    </span>
                </h1>

                @if ($subtitle)
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        {{ $subtitle }}
                    </p>
                @endif
            </div>

            {{-- Bloque derecho: Identificador contextual --}}
            {{-- @if (isset($customer))
                <div class="bg-indigo-50 dark:bg-gray-800 px-4 py-2 rounded-xl shadow-sm">
                    <span class="text-lg font-semibold text-indigo-600 dark:text-indigo-400">
                        {{ $customer->fullName }}
                    </span>
                </div>

            @elseif (isset($supplier))
                <div class="bg-emerald-50 dark:bg-gray-800 px-4 py-2 rounded-xl shadow-sm">
                    <span class="text-lg font-semibold text-emerald-600 dark:text-emerald-400">
                        {{ $supplier->fullName }}
                    </span>
                </div>

            @elseif (isset($meter))
                <div class="bg-blue-50 dark:bg-gray-800 px-4 py-2 rounded-xl shadow-sm">
                    <span class="text-lg font-semibold text-blue-600 dark:text-blue-400">
                        N° {{ $meter->numero_medidor }}
                    </span>
                </div>
            @endif --}}

        </div>
  
    <div class="py-12">
        <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    @if (isset($customer))
                        <livewire:document.customer.customer-documents-table
                            :customer="$customer"
                        />
                    @elseif ($entity === 'customers')
                        <livewire:document.documents-table
                            :entity="$entity"
                        />
                    @elseif (isset($supplier))
                        <livewire:document.documents-table
                            :supplier="$supplier"
                        />
                    @elseif ($entity === 'suppliers')
                        <livewire:document.documents-table
                            :entity="$entity"
                        />
                    @elseif (isset($customer) && isset($meter))
                        <livewire:document.documents-table
                            :customer="$customer"
                            :meter="$meter"
                        />
                    @elseif (isset($meter))
                        <livewire:document.documents-table
                            :meter="$meter"
                        />
                    @elseif ($entity === 'overdue')
                        <livewire:document.overdue-documents-table/>
                    @endif

                </div>
            </div>
        </div>
    </div>


@endsection