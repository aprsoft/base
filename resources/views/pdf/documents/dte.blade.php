<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documento tributario {{ data_get($document, 'folio', data_get($document, 'Folio', '')) }}</title>
    @vite('resources/css/app.css')
    <style>
        @page { size: A4 portrait; margin: 0; }
        @media print {
            html, body { margin: 0 !important; padding: 0 !important; background: #fff !important; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            .no-print { display: none !important; }
            .hoja-dte { width: 210mm !important; min-height: 297mm !important; margin: 0 !important; padding: 12mm !important; box-shadow: none !important; border: none !important; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 antialiased">
@php
    // Ajusta estos alias a los nombres reales de las columnas/relaciones de tu proyecto.
    $dato = static function ($origen, array $rutas, $defecto = null) {
        foreach ($rutas as $ruta) {
            $valor = data_get($origen, $ruta);
            if ($valor !== null && $valor !== '') return $valor;
        }
        return $defecto;
    };
    $dinero = static fn ($valor) => '$' . number_format((float) $valor, 0, ',', '.');
    $empresa = $dato($document, ['company', 'emisor', 'issuer']);
    $receptor = $dato($document, ['customer', 'receptor', 'supplier']);
    $detalle = collect($dato($document, ['details', 'detalle'], []));
    $referencias = collect($dato($document, ['references', 'referencias'], []));

    $tipo = (int) $dato($document, ['TipoDTE', 'tipo_dte', 'dte_type'], 0);
    $nombreTipo = [33 => 'FACTURA ELECTRÓNICA', 34 => 'FACTURA EXENTA ELECTRÓNICA',
        39 => 'BOLETA ELECTRÓNICA', 41 => 'BOLETA EXENTA ELECTRÓNICA',
        56 => 'NOTA DE DÉBITO ELECTRÓNICA', 61 => 'NOTA DE CRÉDITO ELECTRÓNICA'][$tipo]
        ?? $dato($document, ['document_type.name', 'type.name', 'tipo_documento'], 'DOCUMENTO TRIBUTARIO ELECTRÓNICO');
    $folio = $dato($document, ['Folio', 'folio', 'number'], '—');
    $fechaOriginal = $dato($document, ['FchEmis', 'fecha_emision', 'issued_at', 'date', 'fecha']);
    try { $fecha = $fechaOriginal ? \Carbon\Carbon::parse($fechaOriginal)->format('d/m/Y') : '—'; }
    catch (\Throwable $e) { $fecha = (string) $fechaOriginal; }

    $rutEmisor = $dato($document, ['RUTEmisor', 'rut_emisor', 'company.rut', 'company.RUTEmisor'], '—');
    $razonEmisor = $dato($document, ['RznSoc', 'razon_social', 'company.razon_social', 'company.name', 'emisor.name'], 'Emisor');
    $giroEmisor = $dato($document, ['GiroEmis', 'company.giro', 'company.business_activity'], '—');
    $dirEmisor = $dato($document, ['DirOrigen', 'company.address', 'company.direccion'], '—');
    $comunaEmisor = $dato($document, ['CmnaOrigen', 'company.comuna'], '');

    $rutReceptor = $dato($document, ['RUTRecep', 'rut_receptor', 'customer.rut', 'supplier.rut'], '—');
    $razonReceptor = $dato($document, ['RznSocRecep', 'customer.razon_social', 'customer.name', 'supplier.name'], '—');
    $giroReceptor = $dato($document, ['GiroRecep', 'customer.giro', 'supplier.giro'], '—');
    $dirReceptor = $dato($document, ['DirRecep', 'customer.address', 'customer.direccion', 'supplier.address'], '—');
    $comunaReceptor = $dato($document, ['CmnaRecep', 'customer.comuna', 'supplier.comuna'], '');

    $montoDetalle = static function ($item) use ($dato) {
        $total = $dato($item, ['MontoItem', 'monto', 'subtotal', 'total']);
        if ($total !== null) return (float) $total;
        $cantidad = (float) $dato($item, ['QtyItem', 'qty', 'quantity'], 1);
        $precio = (float) $dato($item, ['PrcItem', 'PrcNetoItem', 'price', 'unit_price'], 0);
        return $cantidad * $precio;
    };
    $sumaDetalle = $detalle->sum($montoDetalle);
    $montoTotal = $dato($document, ['MntTotal', 'monto_total', 'total', 'total_amount'], $sumaDetalle);
    $neto = $dato($document, ['MntNeto', 'monto_neto', 'neto']);
    $exento = $dato($document, ['MntExe', 'monto_exento', 'exento']);
    $iva = $dato($document, ['IVA', 'iva', 'monto_iva']);

    // $stamp es PNG binario, tal como lo entrega TimbrePdf417Service::generar().
    $timbreBase64 = isset($stamp) && is_string($stamp) && $stamp !== '' ? base64_encode($stamp) : null;


    $timbreAnchoMm = null;

    if ($timbreBase64 !== null) {
        $dimensiones = getimagesizefromstring($stamp);

        if ($dimensiones !== false) {
            // Imprimir el PNG a los 600 DPI previstos
            // por TimbrePdf417Service.
            $timbreAnchoMm = round(
                $dimensiones[0] * 25.4 / 600,
                2
            );
        }
    }

@endphp

<div class="no-print mx-auto flex max-w-[210mm] justify-end px-3 py-4">
    <button onclick="window.print()" type="button"
            class="rounded-lg bg-sky-700 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-sky-800">
        Imprimir / Guardar PDF
    </button>
</div>

<main class="hoja-dte mx-auto flex min-h-[297mm] w-full max-w-[210mm] flex-col bg-white p-6 shadow-xl sm:p-10">
    <!-- Encabezado del DTE -->
    <header class="flex items-start justify-between gap-5 border-b-4 border-cyan-500 pb-5">
        <div class="min-w-0 flex-1">
            <div class="mb-3 flex items-center gap-3">
                <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-sky-700 text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="size-7">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 3h6l4 4v14H6V3h2Zm5 0v5h5M9 12h6m-6 4h6" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] font-bold uppercase tracking-[.17em] text-sky-600">Documento tributario electrónico</p>
                    <h1 class="break-words text-lg font-extrabold leading-tight text-slate-900">{{ $razonEmisor }}</h1>
                </div>
            </div>
            <div class="space-y-0.5 text-[11px] leading-snug text-slate-600">
                <p><strong>RUT:</strong> {{ $rutEmisor }}</p>
                <p><strong>Giro:</strong> {{ $giroEmisor }}</p>
                <p><strong>Dirección:</strong> {{ $dirEmisor }} {{ $comunaEmisor ? '· ' . $comunaEmisor : '' }}</p>
            </div>
        </div>
        <div class="w-[66mm] shrink-0 border-2 border-sky-700 px-3 py-3 text-center text-sky-900">
            <p class="text-xs font-extrabold">R.U.T.: {{ $rutEmisor }}</p>
            <p class="mt-2 text-sm font-black uppercase leading-tight">{{ $nombreTipo }}</p>
            <p class="mt-2 text-2xl font-black">N° {{ $folio }}</p>
            <p class="mt-1 text-[10px] font-semibold uppercase tracking-wide">S.I.I. — Chile</p>
        </div>
    </header>

    <!-- Resumen tipo boleta -->
    <section class="mt-5 flex items-center justify-between gap-4 rounded-xl border border-sky-200 bg-sky-50 p-4">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-widest text-sky-700">Fecha de emisión</p>
            <p class="mt-1 text-lg font-bold text-slate-900">{{ $fecha }}</p>
        </div>
        <div class="text-right">
            <p class="text-[10px] font-bold uppercase tracking-widest text-sky-700">Monto total del documento</p>
            <p class="mt-1 text-2xl font-black text-sky-900">{{ $dinero($montoTotal) }}</p>
        </div>
    </section>

    <!-- Tarjetas centrales inspiradas en la boleta de referencia -->
    <section class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-[35%_1fr]">
        <div class="flex min-h-36 flex-col justify-between rounded-2xl bg-sky-700 p-5 text-white">
            <p class="text-xs font-semibold uppercase tracking-widest text-cyan-200">Tu documento</p>
            <div>
                <h2 class="text-2xl font-extrabold leading-tight">Detalle de<br>tu cuenta</h2>
                <p class="mt-2 text-xs text-sky-100">Folio {{ $folio }}</p>
            </div>
            <span class="h-1.5 w-16 rounded-full bg-cyan-300"></span>
        </div>
        <div class="rounded-2xl border border-cyan-300 p-4">
            <h2 class="mb-3 border-b border-cyan-200 pb-2 text-xs font-extrabold uppercase tracking-wide text-sky-700">Datos del receptor</h2>
            <dl class="grid grid-cols-[80px_1fr] gap-x-3 gap-y-2 text-[11px] leading-snug">
                <dt class="font-semibold text-slate-500">Nombre</dt><dd class="font-bold text-slate-800">{{ $razonReceptor }}</dd>
                <dt class="font-semibold text-slate-500">RUT</dt><dd>{{ $rutReceptor }}</dd>
                <dt class="font-semibold text-slate-500">Giro</dt><dd>{{ $giroReceptor }}</dd>
                <dt class="font-semibold text-slate-500">Dirección</dt><dd>{{ $dirReceptor }} {{ $comunaReceptor ? '· ' . $comunaReceptor : '' }}</dd>
            </dl>
        </div>
    </section>

    <!-- Ítems del DTE -->
    <section class="mt-5 overflow-hidden rounded-xl border border-cyan-300">
        <h2 class="bg-cyan-50 px-4 py-3 text-xs font-extrabold uppercase tracking-widest text-sky-800">Detalle del documento</h2>
        <table class="w-full table-fixed text-[11px]">
            <thead class="border-y border-cyan-200 bg-white text-left text-slate-600">
                <tr>
                    <th class="w-[47%] px-3 py-2 font-bold">Descripción</th>
                    <th class="w-[12%] px-2 py-2 text-right font-bold">Cant.</th>
                    <th class="w-[20%] px-2 py-2 text-right font-bold">P. unitario</th>
                    <th class="w-[21%] px-3 py-2 text-right font-bold">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($detalle as $item)
                    @php
                        $nombreItem = $dato($item, ['NmbItem', 'name', 'description', 'descripcion'], 'Ítem');
                        $cantidad = $dato($item, ['QtyItem', 'qty', 'quantity'], 1);
                        $precio = $dato($item, ['PrcItem', 'PrcNetoItem', 'price', 'unit_price'], 0);
                    @endphp
                    <tr class="align-top">
                        <td class="break-words px-3 py-2.5">{{ $nombreItem }}</td>
                        <td class="px-2 py-2.5 text-right">{{ $cantidad }}</td>
                        <td class="px-2 py-2.5 text-right">{{ $dinero($precio) }}</td>
                        <td class="px-3 py-2.5 text-right font-semibold">{{ $dinero($montoDetalle($item)) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-3 py-6 text-center text-slate-500">No hay ítems asociados al documento.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <section class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="rounded-xl border border-cyan-300 p-4">
            <h2 class="mb-3 text-xs font-extrabold uppercase tracking-widest text-sky-700">Referencias</h2>
            @forelse ($referencias as $ref)
                <p class="mb-2 text-[11px] leading-relaxed">
                    <span class="font-bold">Tipo:</span> {{ $dato($ref, ['TpoDocRef', 'tipo_documento', 'type'], '—') }}
                    <span class="font-bold">Folio:</span> {{ $dato($ref, ['FolioRef', 'folio', 'number'], '—') }}<br>
                    <span class="font-bold">Fecha:</span> {{ $dato($ref, ['FchRef', 'fecha', 'date'], '—') }}
                </p>
            @empty
                <p class="text-[11px] text-slate-500">Sin referencias registradas.</p>
            @endforelse
        </div>
        <div class="rounded-xl border border-sky-300 bg-sky-50 p-4">
            <h2 class="mb-3 text-xs font-extrabold uppercase tracking-widest text-sky-700">Resumen de montos</h2>
            <dl class="space-y-2 text-xs">
                @if ($neto !== null)
                    <div class="flex justify-between gap-2"><dt>Monto neto</dt><dd>{{ $dinero($neto) }}</dd></div>
                @endif
                @if ($exento !== null)
                    <div class="flex justify-between gap-2"><dt>Monto exento</dt><dd>{{ $dinero($exento) }}</dd></div>
                @endif
                @if ($iva !== null)
                    <div class="flex justify-between gap-2"><dt>IVA</dt><dd>{{ $dinero($iva) }}</dd></div>
                @endif
                <div class="flex justify-between gap-2 border-t border-sky-300 pt-3 text-base font-black text-sky-950">
                    <dt>Total</dt><dd>{{ $dinero($montoTotal) }}</dd>
                </div>
            </dl>
        </div>
    </section>

    <!-- Timbrado PDF417 ya firmado en el XML -->
    <footer class="mt-auto pt-7">
        <div class="grid grid-cols-1 items-end gap-4 border-t border-slate-200 pt-4 sm:grid-cols-[1fr_auto]">
            <div>
                <h2 class="text-xs font-extrabold text-slate-800">Timbre Electrónico SII</h2>
                {{-- @if ($timbreBase64)
                    <img src="data:image/png;base64,{{ $timbreBase64 }}"
                         class="mt-2 h-auto w-[90mm] max-w-full object-contain"
                         alt="Timbre electrónico PDF417 del DTE">
                @else
                    <p class="mt-2 text-xs text-red-700">No se recibió el timbre PDF417.</p>
                @endif --}}

                @if ($timbreBase64 && $timbreAnchoMm !== null)
                    <img
                        src="data:image/png;base64,{{ $timbreBase64 }}"
                        alt="Timbre electrónico PDF417 del DTE"
                        class="mt-2 block"
                        style="width: {{ $timbreAnchoMm }}mm;
                            height: auto;
                            max-width: none;"
                    >
                @else
                    <p class="mt-2 text-xs text-red-700">
                        No se recibió un timbre PDF417 válido como imagen.
                    </p>
                @endif

                <p class="mt-2 text-[10px] text-slate-600">Verifique documento en www.sii.cl</p>
            </div>
            <div class="rounded-xl border-2 border-sky-500 px-5 py-3 text-right">
                <p class="text-[10px] font-semibold uppercase text-sky-700">Total documento</p>
                <p class="text-xl font-black text-sky-950">{{ $dinero($montoTotal) }}</p>
                <p class="mt-1 text-[10px] text-slate-500">Folio {{ $folio }}</p>
            </div>
        </div>
    </footer>
</main>
</body>
</html>
