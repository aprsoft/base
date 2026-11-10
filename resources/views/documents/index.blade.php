@extends('layouts.app')

@section('content')

    <x-common.page-breadcrumb pageTitle="Documentos - Cliente" />

    <div class="space-y-6">

        <div
            class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6"
        >
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                    Documentos
                </h3>

                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    por definir
                </p>
            </div>

            <livewire:document.customer.customer-documents-table />

        </div>

    </div>

@endsection