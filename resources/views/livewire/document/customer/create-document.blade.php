<div
    x-data="{
        alturaPos: 650,
        panelExpandido: null,
        tipoDocumento: 'nota_venta',
        detalle: [],
        modalAbierto: false,
        productoActual: null,
        editando: null,
        cantidad: 1,
        descuentoTipo: 'porcentaje',
        descuentoValor: 0,
        errorModal: '',

        ajustarAltura(elemento) {
            this.alturaPos = Math.max(
                420,
                Math.floor(window.innerHeight - elemento.getBoundingClientRect().top - 20)
            );
        },
        alternarPanel(panel) {
            this.panelExpandido = this.panelExpandido === panel ? null : panel;
        },
        formato(valor) {
            return '$' + Math.round(Number(valor) || 0).toLocaleString('es-CL');
        },
        descuentoItem(item) {
            const base = item.precio * item.cantidad;
            return item.descuentoTipo === 'porcentaje'
                ? Math.round(base * item.descuentoValor / 100)
                : item.descuentoValor;
        },
        get subtotal() {
            return this.detalle.reduce((suma, item) => suma + item.precio * item.cantidad, 0);
        },
        get totalDescuentos() {
            return this.detalle.reduce((suma, item) => suma + this.descuentoItem(item), 0);
        },
        get total() {
            return this.subtotal - this.totalDescuentos;
        },
        abrirModal(producto) {
            this.productoActual = { ...producto };
            this.editando = null;
            this.cantidad = 1;
            this.descuentoTipo = 'porcentaje';
            this.descuentoValor = 0;
            this.errorModal = '';
            this.modalAbierto = true;
        },
        editarItem(indice) {
            const item = this.detalle[indice];
            if (!item) return;
            this.productoActual = { ...item };
            this.editando = indice;
            this.cantidad = item.cantidad;
            this.descuentoTipo = item.descuentoTipo;
            this.descuentoValor = item.descuentoValor;
            this.errorModal = '';
            this.modalAbierto = true;
        },
        cerrarModal() {
            this.modalAbierto = false;
            this.productoActual = null;
            this.editando = null;
            this.errorModal = '';
        },
        get subtotalModal() {
            return this.productoActual ? this.productoActual.precio * Number(this.cantidad) : 0;
        },
        get descuentoModal() {
            const valor = Number(this.descuentoValor);
            return this.descuentoTipo === 'porcentaje'
                ? Math.round(this.subtotalModal * valor / 100)
                : valor;
        },
        get totalModal() {
            return Math.max(0, this.subtotalModal - this.descuentoModal);
        },
        guardarItem() {
            const cantidad = Number(this.cantidad);
            const descuento = Number(this.descuentoValor);
            if (!this.productoActual) return;
            if (!Number.isFinite(cantidad) || cantidad <= 0) {
                this.errorModal = 'Ingrese una cantidad válida.';
                return;
            }
            if (!Number.isFinite(descuento) || descuento < 0) {
                this.errorModal = 'Ingrese un descuento válido.';
                return;
            }
            if (this.descuentoTipo === 'porcentaje' && descuento > 100) {
                this.errorModal = 'El descuento no puede superar el 100%.';
                return;
            }
            if (this.descuentoTipo === 'monto' && descuento > this.subtotalModal) {
                this.errorModal = 'El descuento supera el subtotal.';
                return;
            }
            const item = {
                id: this.productoActual.id,
                nombre: this.productoActual.nombre,
                precio: Number(this.productoActual.precio),
                cantidad,
                descuentoTipo: this.descuentoTipo,
                descuentoValor: descuento
            };
            if (this.editando === null) {
                this.detalle.push(item);
            } else {
                this.detalle.splice(this.editando, 1, item);
            }
            this.cerrarModal();
        },
        eliminarItem(indice) {
            this.detalle.splice(indice, 1);
        }
    }"
    x-init="ajustarAltura($el)"
    x-on:resize.window="ajustarAltura($el)"
    x-bind:style="'--pos-altura:' + alturaPos + 'px'"
    class="flex flex-col gap-3 xl:h-[var(--pos-altura)] xl:min-h-0 xl:overflow-hidden"
>
    <style>[x-cloak] { display: none !important; }</style>

    <header class="flex shrink-0 items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Crear documento</h1>
            <p class="text-xs text-gray-500 dark:text-gray-400">Ventas / Nuevo documento</p>
        </div>
    </header>

    <div class="grid min-h-0 flex-1 grid-cols-1 gap-3 xl:grid-cols-3 xl:overflow-hidden">
        <!-- IZQUIERDA: las grillas crecen sin mover el resto de la pantalla -->
        <div
            class="grid min-h-0 min-w-0 gap-3 xl:col-span-2 xl:h-full"
            x-bind:class="panelExpandido === null ? 'xl:grid-rows-2' : 'xl:grid-rows-1'"
        >
            <!-- CATÁLOGO -->
            <section
                x-show="panelExpandido !== 'detalle'"
                class="flex min-h-64 min-w-0 flex-col overflow-hidden rounded-xl border border-gray-200 bg-white p-4 xl:min-h-0 dark:border-gray-700 dark:bg-gray-800"
            >
                <div class="mb-3 flex shrink-0 items-center justify-between gap-2">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Productos y servicios</h2>
                    <button
                        type="button"
                        x-on:click="alternarPanel('catalogo')"
                        x-bind:aria-label="panelExpandido === 'catalogo' ? 'Restaurar ambas grillas' : 'Ampliar productos y servicios'"
                        x-bind:title="panelExpandido === 'catalogo' ? 'Restaurar' : 'Ampliar hacia abajo'"
                        class="shrink-0 rounded-md border border-gray-300 p-1.5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700"
                    >
                        <!-- Expandir catálogo hacia abajo -->
                        <svg x-show="panelExpandido !== 'catalogo'" aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 5 6 6 6-6M6 12l6 6 6-6" />
                        </svg>
                        <!-- Restaurar catálogo hacia arriba -->
                        <svg x-show="panelExpandido === 'catalogo'" x-cloak aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 12 6-6 6 6M6 19l6-6 6 6" />
                        </svg>
                    </button>
                </div>

                <input
                    type="search"
                    wire:model.live.debounce.300ms="busquedaServicio"
                    placeholder="Buscar por ID o descripción..."
                    aria-label="Buscar productos y servicios"
                    class="mb-3 w-full shrink-0 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-900 dark:text-white"
                >

                <!-- Solo la tabla se desplaza; buscador y cabecera fijos -->
                <div class="min-h-0 flex-1 overflow-auto overscroll-contain">
                    <table class="w-full text-left text-sm">
                        <thead class="sticky top-0 z-10 bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-3 py-2">ID</th>
                                <th class="px-3 py-2">Descripción</th>
                                <th class="px-3 py-2 text-right">Valor</th>
                                <th class="px-3 py-2 text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse ($this->servicios as $servicio)
                                <tr wire:key="servicio-{{ $servicio->id }}">
                                    <td class="px-3 py-2">{{ $servicio->id }}</td>
                                    <td class="px-3 py-2">{{ $servicio->name }}</td>
                                    <td class="px-3 py-2 text-right whitespace-nowrap">
                                        {{ $servicio->valor === null ? '—' : '$' . number_format($servicio->valor, 0, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        @if ($servicio->valor !== null)
                                            <button
                                                type="button"
                                                x-on:click="abrirModal({{ \Illuminate\Support\Js::from([
                                                    'id' => $servicio->id,
                                                    'nombre' => $servicio->name,
                                                    'precio' => $servicio->valor,
                                                ]) }})"
                                                class="rounded-md bg-blue-600 px-2.5 py-1 text-xs font-medium text-white hover:bg-blue-700"
                                            >Agregar</button>
                                        @else
                                            <span class="text-xs text-gray-400">Sin valor</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-3 py-6 text-center text-gray-500">No se encontraron servicios.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <p class="mt-2 shrink-0 text-xs text-gray-400">Hasta 40 resultados por búsqueda.</p>
            </section>

            <!-- SELECCIONADOS -->
            <section
                x-show="panelExpandido !== 'catalogo'"
                class="flex min-h-64 min-w-0 flex-col overflow-hidden rounded-xl border border-gray-200 bg-white p-4 xl:min-h-0 dark:border-gray-700 dark:bg-gray-800"
            >
                <div class="mb-3 flex shrink-0 items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Productos seleccionados</h2>
                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs dark:bg-gray-700" x-text="detalle.length + ' ítems'"></span>
                    </div>
                    <button
                        type="button"
                        x-on:click="alternarPanel('detalle')"
                        x-bind:aria-label="panelExpandido === 'detalle' ? 'Restaurar ambas grillas' : 'Ampliar productos seleccionados'"
                        x-bind:title="panelExpandido === 'detalle' ? 'Restaurar' : 'Ampliar hacia arriba'"
                        class="shrink-0 rounded-md border border-gray-300 p-1.5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700"
                    >
                        <!-- Expandir seleccionados hacia arriba -->
                        <svg x-show="panelExpandido !== 'detalle'" aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 12 6-6 6 6M6 19l6-6 6 6" />
                        </svg>
                        <!-- Restaurar seleccionados hacia abajo -->
                        <svg x-show="panelExpandido === 'detalle'" x-cloak aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 5 6 6 6-6M6 12l6 6 6-6" />
                        </svg>
                    </button>
                </div>

                <div class="min-h-0 flex-1 overflow-auto overscroll-contain">
                    <table class="w-full text-left text-sm">
                        <thead class="sticky top-0 z-10 bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-3 py-2">Descripción</th>
                                <th class="px-3 py-2 text-right">Cant.</th>
                                <th class="px-3 py-2 text-right">Precio</th>
                                <th class="px-3 py-2 text-right">Desc.</th>
                                <th class="px-3 py-2 text-right">Total</th>
                                <th class="px-3 py-2 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <template x-for="(item, indice) in detalle" :key="indice">
                                <tr>
                                    <td class="px-3 py-2" x-text="item.nombre"></td>
                                    <td class="px-3 py-2 text-right" x-text="item.cantidad"></td>
                                    <td class="px-3 py-2 text-right whitespace-nowrap" x-text="formato(item.precio)"></td>
                                    <td class="px-3 py-2 text-right whitespace-nowrap" x-text="item.descuentoTipo === 'porcentaje' ? item.descuentoValor + '%' : formato(item.descuentoValor)"></td>
                                    <td class="px-3 py-2 text-right whitespace-nowrap" x-text="formato(item.precio * item.cantidad - descuentoItem(item))"></td>
                                    <td class="px-3 py-2 text-center whitespace-nowrap">
                                        <button type="button" x-on:click="editarItem(indice)" class="mr-2 text-xs font-medium text-blue-600 hover:underline">Editar</button>
                                        <button type="button" x-on:click="eliminarItem(indice)" class="text-xs font-medium text-red-600 hover:underline">Eliminar</button>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="detalle.length === 0">
                                <td colspan="6" class="px-3 py-8 text-center text-gray-500">No hay productos seleccionados.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <!-- DERECHA: desplazamiento únicamente en datos, no en totales/botón -->
        <aside class="flex min-h-0 min-w-0 flex-col gap-3 xl:h-full xl:overflow-hidden">
            <div class="space-y-3 xl:min-h-0 xl:flex-1 xl:overflow-y-auto xl:pr-1">
                <section class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                    <h2 class="mb-3 text-base font-semibold text-gray-900 dark:text-white">Documento</h2>
                    <label class="mb-1 block text-sm font-medium" for="pos-documento-tipo">Tipo de documento</label>
                    <select id="pos-documento-tipo" x-model="tipoDocumento" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900">
                        <option value="nota_venta">Nota de venta</option>
                        <option value="33">Factura electrónica</option>
                        <option value="34">Factura exenta</option>
                        <option value="39">Boleta electrónica</option>
                        <option value="41">Boleta exenta</option>
                        <option value="61">Nota de crédito</option>
                    </select>
                    <label class="mb-1 mt-3 block text-sm font-medium">Fecha de emisión</label>
                    <input type="text" readonly value="{{ now()->format('d/m/Y') }}" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900">
                </section>

                <section class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                    <h2 class="mb-3 text-base font-semibold text-gray-900 dark:text-white">Cliente</h2>
                    <label class="flex cursor-pointer items-center gap-2 text-sm">
                        <input type="checkbox" wire:model.live="clienteGenerico" class="size-4 rounded border-gray-300 text-blue-600">
                        
                        <span>Cliente genérico</span>
                    </label>
                    @if (!$clienteGenerico)
                        <div class="mt-4 space-y-3">
                            <div>
                                <a href="{{ route('customers.create') }}" class="flex justify-center rounded-lg border border-blue-600 px-3 py-2 text-sm font-medium text-blue-600 hover:bg-blue-50 dark:hover:bg-gray-700">+ Crear cliente</a>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium">Buscar cliente</label>
                                <input type="search" wire:model.live.debounce.300ms="busquedaCliente" placeholder="Buscar por RUT o nombre..." class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900">
                            </div>
                            @if (mb_strlen(trim($busquedaCliente)) >= 2)
                                <div class="max-h-40 overflow-y-auto rounded-lg border border-gray-200 dark:border-gray-700">
                                    @forelse ($this->clientes as $clienteOpcion)
                                        <button type="button" wire:key="cliente-{{ $clienteOpcion->id }}" wire:click="seleccionarCliente({{ $clienteOpcion->id }})" class="block w-full border-b border-gray-100 px-3 py-2 text-left text-sm hover:bg-blue-50 dark:border-gray-700 dark:hover:bg-gray-700">
                                            <span class="block font-medium">
                                                {{ $clienteOpcion->condicion_cliente === 'EMPRESA'
                                                    ? $clienteOpcion->nombres
                                                    : trim($clienteOpcion->nombres . ' ' . $clienteOpcion->apellido_paterno . ' ' . $clienteOpcion->apellido_materno) }}
                                            </span>
                                            <span class="text-xs text-gray-500">{{ $clienteOpcion->rut }}</span>
                                        </button>
                                    @empty
                                        <p class="p-3 text-sm text-gray-500">No se encontraron clientes.</p>
                                    @endforelse
                                </div>
                            @endif
                            @php
                                $clienteActual = $this->clienteSeleccionado;
                                $nombreCliente = $clienteActual
                                    ? ($clienteActual->condicion_cliente === 'EMPRESA'
                                        ? $clienteActual->nombres
                                        : trim($clienteActual->nombres . ' ' . $clienteActual->apellido_paterno . ' ' . $clienteActual->apellido_materno))
                                    : '';
                            @endphp
                            @if ($clienteActual)
                                <button type="button" wire:click="limpiarCliente" class="text-sm text-blue-600 hover:underline">Cambiar cliente</button>
                            @endif
                            <div class="grid gap-2">
                                <label class="text-xs font-medium">RUT
                                    <input type="text" readonly value="{{ $clienteActual?->rut ?? '' }}" class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900">
                                </label>
                                <label class="text-xs font-medium">Nombre / Razón social
                                    <input type="text" readonly value="{{ $nombreCliente }}" class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900">
                                </label>
                                <label class="text-xs font-medium">Giro
                                    <input type="text" readonly value="{{ $clienteActual?->giro ?? '' }}" class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900">
                                </label>
                                <label class="text-xs font-medium">Dirección
                                    <input type="text" readonly value="{{ $clienteActual?->direccion_cliente ?? '' }}" class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900">
                                </label>
                                <label class="text-xs font-medium">Comuna
                                    <input type="text" readonly value="{{ $clienteActual?->comuna ?? '' }}" class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900">
                                </label>
                            </div>
                        </div>
                    @endif
                </section>
            </div>

            <section class="shrink-0 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                <h2 class="mb-3 text-base font-semibold text-gray-900 dark:text-white">Resumen de la compra</h2>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span>Subtotal</span><span x-text="formato(subtotal)"></span></div>
                    <div class="flex justify-between"><span>Descuentos</span><span x-text="formato(totalDescuentos)"></span></div>
                    <div class="flex justify-between"><span>Neto</span><span>—</span></div>
                    <div class="flex justify-between"><span>Exento</span><span>—</span></div>
                    <div class="flex justify-between"><span>IVA</span><span>—</span></div>
                </div>
                <div class="mt-3 flex items-end justify-between border-t border-gray-200 pt-3 dark:border-gray-700">
                    <span class="text-sm font-semibold">TOTAL</span>
                    <strong class="text-2xl text-gray-900 dark:text-white" x-text="formato(total)"></strong>
                </div>
            </section>
            <div class="shrink-0">
                <button x-show="tipoDocumento === 'nota_venta'" type="button" disabled title="Pendiente de implementación" class="w-full rounded-lg bg-blue-600 px-4 py-3 text-sm font-semibold text-white opacity-70">Enviar a caja</button>
                <button x-show="tipoDocumento !== 'nota_venta'" x-cloak type="button" disabled title="Pendiente de implementación" class="w-full rounded-lg bg-green-600 px-4 py-3 text-sm font-semibold text-white opacity-70">Crear documento</button>
            </div>
        </aside>
    </div>

    <!-- MODAL: agregar y editar ítems -->
    <div
        x-show="modalAbierto"
        x-cloak
        x-transition.opacity
        x-on:keydown.escape.window="cerrarModal()"
        x-on:click.self="cerrarModal()"
        class="fixed inset-0 z-9999 flex items-center justify-center bg-black/60 p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="titulo-modal-pos"
    >
        <div class="max-h-[calc(100dvh-2rem)] w-full max-w-lg overflow-y-auto rounded-xl bg-white p-6 shadow-xl dark:bg-gray-800" x-on:click.stop>
            <h3 id="titulo-modal-pos" class="mb-4 text-lg font-semibold text-gray-900 dark:text-white" x-text="editando === null ? 'Agregar producto o servicio' : 'Editar producto o servicio'"></h3>
            <div class="mb-4 rounded-lg bg-gray-50 p-3 dark:bg-gray-900">
                <div class="font-medium" x-text="productoActual?.nombre"></div>
                <div class="mt-1 text-sm text-gray-500">Precio unitario: <span x-text="formato(productoActual?.precio ?? 0)"></span></div>
            </div>
            <form x-on:submit.prevent="guardarItem()" class="space-y-4">
                <div>
                    <label class="mb-1 block text-sm font-medium">Cantidad</label>
                    <input type="number" min="0.001" step="0.001" required x-model.number="cantidad" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 dark:border-gray-600 dark:bg-gray-900">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Tipo de descuento</label>
                    <select x-model="descuentoTipo" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 dark:border-gray-600 dark:bg-gray-900">
                        <option value="porcentaje">Porcentaje (%)</option>
                        <option value="monto">Monto ($)</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Descuento</label>
                    <input type="number" min="0" step="any" required x-model.number="descuentoValor" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 dark:border-gray-600 dark:bg-gray-900">
                </div>
                <p x-show="errorModal" x-text="errorModal" class="text-sm text-red-600"></p>
                <div class="space-y-2 rounded-lg bg-gray-50 p-3 text-sm dark:bg-gray-900">
                    <div class="flex justify-between"><span>Subtotal</span><span x-text="formato(subtotalModal)"></span></div>
                    <div class="flex justify-between"><span>Descuento</span><span x-text="formato(descuentoModal)"></span></div>
                    <div class="flex justify-between border-t pt-2 text-base font-semibold"><span>Total</span><span x-text="formato(totalModal)"></span></div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" x-on:click="cerrarModal()" class="rounded-lg border border-gray-300 px-4 py-2 text-sm dark:border-gray-600">Cancelar</button>
                    <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700" x-text="editando === null ? 'Agregar' : 'Guardar cambios'"></button>
                </div>
            </form>
        </div>
    </div>
</div>