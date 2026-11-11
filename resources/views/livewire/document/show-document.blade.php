<div class="space-y-6">
    
    {{-- ============================= --}}
    {{-- HEADER --}}
    {{-- ============================= --}}
    
    <h2 class="font-semibold text-2xl text-gray-800 dark:text-gray-200 leading-tight">
        Información del Documento
    </h2>
        <div>
            @can('view', $document)

                <livewire:pdf.pdf-actions
                    type="document.dte"
                    :record-id="$document->id"
                    :key="'document-pdf-'.$document->id"
                />

            @endcan
        </div>


    {{-- ============================= --}}
    {{-- CONTENIDO --}}
    {{-- ============================= --}}
    <div class="py-10">

        <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-xl rounded-2xl overflow-hidden">

                <div class="p-8">

                    {{-- GRID PRINCIPAL --}}
                    <div class="grid grid-cols-1 xl:grid-cols-4 gap-10">

                        {{-- ========================================= --}}
                        {{-- COLUMNA 1 — DATOS DOCUMENTO --}}
                        {{-- ========================================= --}}
                        <div class="space-y-6">

                            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 border-b pb-2">
                                Datos del Documento
                            </h3>

                            <div>
                                <span class="block text-xs uppercase tracking-wide text-gray-500">
                                    Tipo
                                </span>
                                <p class="mt-1 font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $document->typedocument->name }} N° {{ $document->folio }}
                                </p>
                            </div>

                            <div>
                                <span class="block text-xs uppercase tracking-wide text-gray-500">
                                    Estado
                                </span>
                                <p class="mt-1 font-semibold text-gray-800 dark:text-gray-200">
                                    {{ $document->condicion_documento }}
                                </p>
                            </div>

                            @if ($document->parent_id)
                                <div>
                                    <span class="block text-xs uppercase tracking-wide text-gray-500">
                                        Documento Referencia
                                    </span>
                                    <p class="mt-1 font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $document->parent->typedocument->name }} N° {{ $document->parent->folio }}
                                    </p>
                                </div>
                            @endif

                            <div>
                                <span class="block text-xs uppercase tracking-wide text-gray-500">
                                    Fecha Emision
                                </span>
                                <p class="mt-1 font-semibold text-gray-800 dark:text-gray-200">
                                    {{ $document->fecha }}
                                </p>
                            </div>

                            @if ($document->payment->isNotEmpty())
                                <div>
                                    <span class="block text-xs uppercase tracking-wide text-gray-500">
                                        Fecha Pago
                                    </span>
                                    <p class="mt-1 font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $document->payment->first()->fecha }}
                                    </p>
                                </div>
                            @endif

                        </div>


                        {{-- ========================================= --}}
                        {{-- COLUMNA 2 — CLIENTE / PROVEEDOR --}}
                        {{-- ========================================= --}}
                        <div class="space-y-6">

                            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 border-b pb-2">
                                @if ($document->customer)
                                    Datos Cliente 
                                @else
                                    Datos Proveedor                               
                                @endif
                            </h3>

                            <div>
                                <span class="block text-xs uppercase tracking-wide text-gray-500">
                                    Razón Social
                                </span>
                                <p class="mt-1 font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $document->customer?->fullName ?? $document->supplier->fullName }}
                                </p>
                            </div>

                            <div>
                                <span class="block text-xs uppercase tracking-wide text-gray-500">
                                    Dirección
                                </span>
                                <p class="mt-1 font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $document->customer?->direccion_cliente ?? $document->supplier->direccion_proveedor }}
                                </p>
                            </div>

                            @if($document->customer)
                                <div>
                                    <span class="block text-xs uppercase tracking-wide text-gray-500">
                                        Email
                                    </span>
                                    <p class="mt-1 font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $document->customer->email_cliente }}
                                    </p>
                                </div>
                            @endif

                        </div>


                        {{-- ========================================= --}}
                        {{-- COLUMNA 3 — INFORMACIÓN CONSUMO --}}
                        {{-- ========================================= --}}
                        <div class="space-y-6">

                            @if ($document->reading)

                                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 border-b pb-2">
                                    Información de Consumo
                                </h3>

                                <div>
                                    <span class="block text-xs uppercase tracking-wide text-gray-500">
                                        Medidor
                                    </span>
                                    <p class="mt-1 font-semibold text-gray-900 dark:text-gray-100">
                                        N° {{ $document->meter->numero_medidor }}
                                    </p>
                                </div>

                                <div>
                                    <span class="block text-xs uppercase tracking-wide text-gray-500">
                                        Lectura Actual
                                    </span>
                                    <p class="mt-1 font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $document->reading->lectura }}
                                    </p>
                                </div>

                                <div>
                                    <span class="block text-xs uppercase tracking-wide text-gray-500">
                                        Lectura Anterior
                                    </span>
                                    <p class="mt-1 font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $document->reading->previous_reading }}
                                    </p>
                                </div>

                                <div>
                                    <span class="block text-xs uppercase tracking-wide text-gray-500">
                                        Consumo
                                    </span>
                                    <p class="mt-1 font-bold text-blue-600 dark:text-blue-400 text-lg">
                                        {{ $document->reading->lectura - $document->reading->previous_reading }} m³
                                    </p>
                                </div>

                            @endif

                        </div>


                        {{-- ========================================= --}}
                        {{-- COLUMNA 4 — ACCIONES --}}
                        {{-- ========================================= --}}
                        <div class="space-y-6">

                            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 border-b pb-2">
                                Acciones
                            </h3>

                           

                            {{-- <div class="flex flex-col gap-4">                                

                                <x-buttons.pdf-action-button
                                    pdfAction="print"
                                    :pdfData="$document"
                                    class="w-full h-11 flex items-center justify-center"
                                />

                                <x-buttons.pdf-action-button
                                    pdfAction="mail"
                                    :pdfData="$document"
                                    class="w-full h-11 flex items-center justify-center"
                                />


                            </div> --}}

                        </div>
                       

                    </div>
               

                          
                    {{-- ========================================= --}}
                    {{-- TABLA DETALLE --}}
                    {{-- ========================================= --}}
                    <div class="mt-12">
                        <livewire:detail.detail-table :document-id="$document->id" />
                    </div>

                </div>

            </div>
        </div>
    </div>

</div>