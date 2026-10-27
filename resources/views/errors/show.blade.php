@extends('layouts.app')

@section('content')

    <x-common.page-breadcrumb pageTitle="Detalle del Error" />

    <div class="space-y-6">

        {{-- Información general --}}
        <div
            class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6"
        >
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                        Información del Error
                    </h3>

                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Información general del error registrado por el sistema.
                    </p>
                </div>

                <div class="flex items-center gap-3">

                    <a
                        href="{{ route('errors.index') }}"
                        class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]"
                    >
                        Volver
                    </a>

                </div>

            </div>

            <div class="mt-6 border-t border-gray-100 pt-6 dark:border-gray-800">

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">

                    {{-- ID --}}
                    <div>
                        <p class="mb-1 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            ID
                        </p>

                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                            {{ $errorLog->id }}
                        </p>
                    </div>

                    {{-- Inquilino --}}
                    {{-- <div>
                        <p class="mb-1 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Inquilino   
                        </p>

                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                            {{ $errorLog->tenant_id ?? '-' }}
                        </p>
                    </div> --}}

                    {{-- Línea --}}
                    <div>
                        <p class="mb-1 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Línea
                        </p>

                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                            {{ $errorLog->line ?? '-' }}
                        </p>
                    </div>

                    {{-- Fecha --}}
                    <div>
                        <p class="mb-1 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Fecha
                        </p>

                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                            {{ $errorLog->created_at?->format('d/m/Y H:i:s') ?? '-' }}
                        </p>
                    </div>

                </div>

            </div>

        </div>


        {{-- Archivo --}}
        <div
            class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6"
        >
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                Archivo
            </h3>

            <div class="mt-4 rounded-lg bg-gray-50 p-4 dark:bg-white/[0.03]">

                <p class="break-all font-mono text-sm text-gray-700 dark:text-gray-300">
                    {{ $errorLog->file ?? '-' }}
                </p>

            </div>
        </div>


        {{-- Mensaje --}}
        <div
            class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6"
        >
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                Mensaje
            </h3>

            <div class="mt-4 rounded-lg bg-gray-50 p-4 dark:bg-white/[0.03]">

                <p class="whitespace-pre-wrap break-words text-sm text-gray-700 dark:text-gray-300">{{ $errorLog->message ?? '-' }}</p>

            </div>
        </div>


        {{-- Excepción --}}
        <div
            class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6"
        >
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                Excepción
            </h3>

            <div class="mt-4 overflow-x-auto rounded-lg bg-gray-50 p-4 dark:bg-white/[0.03]">

                <pre class="whitespace-pre-wrap break-words font-mono text-sm text-gray-700 dark:text-gray-300">{{ $errorLog->exception ?? '-' }}</pre>

            </div>
        </div>


        {{-- Errores adicionales --}}
        <div
            class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6"
        >
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                Errores Adicionales
            </h3>

            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Información estructurada almacenada junto al error.
            </p>

            <div class="mt-4 overflow-x-auto rounded-lg bg-gray-50 p-4 dark:bg-white/[0.03]">

                @if (!empty($errorLog->errors))

                    <pre class="whitespace-pre-wrap break-words font-mono text-sm text-gray-700 dark:text-gray-300">{{ json_encode(
                            $errorLog->errors,
                            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                        ) }}</pre>

                @else

                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        No existen errores adicionales registrados.
                    </p>

                @endif

            </div>

        </div>

    </div>

@endsection