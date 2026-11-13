
<div
    x-data="{
        // Estado de la interfaz
        clienteGenerico: true,
        tipoDocumento: 'nota_venta',
        busqueda: '',
        modalAbierto: false,
        productoActual: null,
        editando: null,
        cantidad: 1,
        descuentoTipo: 'porcentaje',
        descuentoValor: 0,
        errorModal: '',

        // Datos de demostración. No provienen de la BD.
        catalogo: [
            { id: 1, codigo: 'P001', nombre: 'Producto A', precio: 10000 },
            { id: 2, codigo: 'S001', nombre: 'Servicio B', precio: 5000 },
            { id: 3, codigo: 'P002', nombre: 'Producto C', precio: 8000 }
        ],

        detalle: [],

        get productosFiltrados() {
            const texto = this.busqueda.trim().toLowerCase();

            return this.catalogo.filter(p =>
                (p.codigo + ' ' + p.nombre)
                    .toLowerCase()
                    .includes(texto)
            );
        },

        get subtotal() {
            return this.detalle.reduce(
                (total, item) => total + item.precio * item.cantidad,
                0
            );
        },

        descuentoItem(item) {
            const base = item.precio * item.cantidad;

            return item.descuentoTipo === 'porcentaje'
                ? Math.round(base * item.descuentoValor / 100)
                : item.descuentoValor;
        },

        get totalDescuentos() {
            return this.detalle.reduce(
                (total, item) => total + this.descuentoItem(item),
                0
            );
        },

        // Total comercial de demostración, sin desglose tributario.
        get total() {
            return this.subtotal - this.totalDescuentos;
        },

        formato(valor) {
            return '$' + Math.round(valor).toLocaleString('es-CL');
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

        editarItem(index) {
            const item = this.detalle[index];

            if (!item) return;

            this.productoActual = { ...item };
            this.editando = index;
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
            if (!this.productoActual) return 0;

            return this.productoActual.precio * Number(this.cantidad);
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
            const subtotal = this.subtotalModal;

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
                this.errorModal = 'El porcentaje no puede superar el 100%.';
                return;
            }

            if (this.descuentoTipo === 'monto' && descuento > subtotal) {
                this.errorModal = 'El descuento supera el subtotal.';
                return;
            }

            const item = {
                id: this.productoActual.id,
                codigo: this.productoActual.codigo,
                nombre: this.productoActual.nombre,
                precio: this.productoActual.precio,
                cantidad: cantidad,
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

        eliminarItem(index) {
            this.detalle.splice(index, 1);
        }
    }"
    class="space-y-6"
>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    <!-- ENCABEZADO -->
    <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
            Crear documento
        </h1>

        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Ventas / Nuevo documento
        </p>
    </div>

    <!-- DOS COLUMNAS -->
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

        <!-- COLUMNA IZQUIERDA: 2/3 -->
        <div class="space-y-6 xl:col-span-2">

            <!-- CATÁLOGO -->
            <section class="rounded-xl border border-gray-200 bg-white p-5
                            dark:border-gray-700 dark:bg-gray-800">

                <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                    Productos y servicios
                </h2>

                <input
                    type="search"
                    x-model="busqueda"
                    placeholder="Buscar por código o descripción..."
                    class="mb-4 w-full rounded-lg border border-gray-300
                           bg-white px-4 py-2.5 text-sm text-gray-900
                           focus:border-blue-500 focus:outline-none
                           dark:border-gray-600 dark:bg-gray-900 dark:text-white"
                >

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-gray-200 bg-gray-50
                                      dark:border-gray-700 dark:bg-gray-900">
                            <tr>
                                <th class="px-3 py-3">Código</th>
                                <th class="px-3 py-3">Descripción</th>
                                <th class="px-3 py-3 text-right">Precio</th>
                                <th class="px-3 py-3 text-center">Acción</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <template x-for="producto in productosFiltrados"
                                      :key="producto.id">
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-3 py-3" x-text="producto.codigo"></td>

                                    <td class="px-3 py-3" x-text="producto.nombre"></td>

                                    <td class="px-3 py-3 text-right"
                                        x-text="formato(producto.precio)">
                                    </td>

                                    <td class="px-3 py-3 text-center">
                                        <button
                                            type="button"
                                            x-on:click="abrirModal(producto)"
                                            class="rounded-lg bg-blue-600 px-3 py-1.5
                                                   text-xs font-medium text-white
                                                   hover:bg-blue-700"
                                        >
                                            Agregar
                                        </button>
                                    </td>
                                </tr>
                            </template>

                            <tr x-show="productosFiltrados.length === 0">
                                <td colspan="4"
                                    class="px-3 py-8 text-center text-gray-500">
                                    No se encontraron resultados.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <p class="mt-3 text-xs text-gray-400">
                    Catálogo de demostración
                </p>
            </section>

            <!-- DETALLE -->
            <section class="rounded-xl border border-gray-200 bg-white p-5
                            dark:border-gray-700 dark:bg-gray-800">

                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Detalle de la venta
                    </h2>

                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs
                                 dark:bg-gray-700"
                          x-text="detalle.length + ' ítems'">
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-gray-200 bg-gray-50
                                      dark:border-gray-700 dark:bg-gray-900">
                            <tr>
                                <th class="px-3 py-3">Descripción</th>
                                <th class="px-3 py-3 text-right">Cant.</th>
                                <th class="px-3 py-3 text-right">Precio</th>
                                <th class="px-3 py-3 text-right">Desc.</th>
                                <th class="px-3 py-3 text-right">Total</th>
                                <th class="px-3 py-3 text-center">Acción</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <template x-for="(item, index) in detalle" :key="index">
                                <tr>
                                    <td class="px-3 py-3" x-text="item.nombre"></td>

                                    <td class="px-3 py-3 text-right"
                                        x-text="item.cantidad">
                                    </td>

                                    <td class="px-3 py-3 text-right"
                                        x-text="formato(item.precio)">
                                    </td>

                                    <td class="px-3 py-3 text-right"
                                        x-text="item.descuentoTipo === 'porcentaje'
                                            ? item.descuentoValor + '%'
                                            : formato(item.descuentoValor)">
                                    </td>

                                    <td class="px-3 py-3 text-right font-medium"
                                        x-text="formato(
                                            item.precio * item.cantidad
                                            - descuentoItem(item)
                                        )">
                                    </td>

                                    <td class="px-3 py-3">
                                        <div class="flex justify-center gap-2">
                                            <button
                                                type="button"
                                                x-on:click="editarItem(index)"
                                                class="text-blue-600 hover:underline"
                                            >
                                                Editar
                                            </button>

                                            <button
                                                type="button"
                                                x-on:click="eliminarItem(index)"
                                                class="text-red-600 hover:underline"
                                            >
                                                Eliminar
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>

                            <tr x-show="detalle.length === 0">
                                <td colspan="6"
                                    class="px-3 py-10 text-center text-gray-400">
                                    No hay productos o servicios agregados.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <!-- COLUMNA DERECHA: 1/3 -->
        <aside class="space-y-6 self-start xl:sticky xl:top-6">

            <!-- TIPO DE DOCUMENTO -->
            <section class="rounded-xl border border-gray-200 bg-white p-5
                            dark:border-gray-700 dark:bg-gray-800">

                <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                    Documento
                </h2>

                <label class="mb-1 block text-sm font-medium">
                    Tipo de documento
                </label>

                <select
                    x-model="tipoDocumento"
                    class="w-full rounded-lg border border-gray-300
                           bg-white px-3 py-2.5 text-sm
                           dark:border-gray-600 dark:bg-gray-900"
                >
                    <option value="nota_venta">Nota de venta</option>
                    <option value="33">Factura electrónica</option>
                    <option value="34">Factura exenta</option>
                    <option value="39">Boleta electrónica</option>
                    <option value="41">Boleta exenta</option>
                    <option value="61">Nota de crédito</option>
                </select>

                <label class="mb-1 mt-4 block text-sm font-medium">
                    Fecha de emisión
                </label>

                <input
                    type="text"
                    value="{{ now()->format('d/m/Y') }}"
                    readonly
                    class="w-full rounded-lg border border-gray-300
                           bg-gray-50 px-3 py-2.5 text-sm
                           dark:border-gray-600 dark:bg-gray-900"
                >
            </section>

            <!-- CLIENTE -->
            <section class="rounded-xl border border-gray-200 bg-white p-5
                            dark:border-gray-700 dark:bg-gray-800">

                <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                    Cliente
                </h2>

                <!-- MARCADO POR DEFECTO -->
                <label class="flex cursor-pointer items-center gap-3">
                    <input
                        type="checkbox"
                        x-model="clienteGenerico"
                        class="h-4 w-4 rounded border-gray-300 text-blue-600
                               focus:ring-blue-500"
                    >

                    <span class="text-sm font-medium">
                        Cliente genérico
                    </span>
                </label>

                <!-- SOLO APARECE AL DESMARCAR -->
                <div x-show="!clienteGenerico"
                     x-cloak
                     x-transition
                     class="mt-5 space-y-4">

                    <div>
                        <label class="mb-1 block text-sm font-medium">
                            Buscar cliente
                        </label>

                        <input
                            type="search"
                            placeholder="Buscar por RUT o nombre..."
                            class="w-full rounded-lg border border-gray-300
                                   bg-white px-3 py-2.5 text-sm
                                   dark:border-gray-600 dark:bg-gray-900"
                        >
                    </div>

                    <!-- RUTA EXISTENTE EN BASE -->
                    <a
                        href="{{ route('customers.create') }}"
                        class="flex w-full items-center justify-center gap-2
                               rounded-lg border border-blue-600 px-4 py-2.5
                               text-sm font-medium text-blue-600
                               hover:bg-blue-50 dark:hover:bg-gray-700"
                    >
                        + Crear cliente
                    </a>

                    <div>
                        <label class="mb-1 block text-sm font-medium">RUT</label>
                        <input type="text" placeholder="12.345.678-9"
                               class="w-full rounded-lg border border-gray-300
                                      bg-white px-3 py-2.5 text-sm
                                      dark:border-gray-600 dark:bg-gray-900">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium">
                            Nombre / Razón social
                        </label>
                        <input type="text" placeholder="Nombre del cliente"
                               class="w-full rounded-lg border border-gray-300
                                      bg-white px-3 py-2.5 text-sm
                                      dark:border-gray-600 dark:bg-gray-900">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium">Giro</label>
                        <input type="text" placeholder="Giro comercial"
                               class="w-full rounded-lg border border-gray-300
                                      bg-white px-3 py-2.5 text-sm
                                      dark:border-gray-600 dark:bg-gray-900">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium">Dirección</label>
                        <input type="text" placeholder="Dirección"
                               class="w-full rounded-lg border border-gray-300
                                      bg-white px-3 py-2.5 text-sm
                                      dark:border-gray-600 dark:bg-gray-900">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium">Comuna</label>
                        <input type="text" placeholder="Comuna"
                               class="w-full rounded-lg border border-gray-300
                                      bg-white px-3 py-2.5 text-sm
                                      dark:border-gray-600 dark:bg-gray-900">
                    </div>
                </div>
            </section>

            <!-- RESUMEN -->
            <section class="rounded-xl border border-gray-200 bg-white p-5
                            dark:border-gray-700 dark:bg-gray-800">

                <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                    Resumen de la compra
                </h2>

                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span>Subtotal</span>
                        <span x-text="formato(subtotal)"></span>
                    </div>

                    <div class="flex justify-between">
                        <span>Descuentos</span>
                        <span x-text="formato(totalDescuentos)"></span>
                    </div>

                    <div class="flex justify-between">
                        <span>Neto</span>
                        <span>—</span>
                    </div>

                    <div class="flex justify-between">
                        <span>Exento</span>
                        <span>—</span>
                    </div>

                    <div class="flex justify-between">
                        <span>IVA</span>
                        <span>—</span>
                    </div>
                </div>

                <div class="mt-5 border-t border-gray-200 pt-4
                            dark:border-gray-700">
                    <span class="text-sm font-semibold">
                        MONTO TOTAL
                    </span>

                    <div class="mt-1 text-3xl font-bold text-gray-900
                                dark:text-white"
                         x-text="formato(total)">
                    </div>
                </div>
            </section>

            <!-- BOTONES SEGÚN DOCUMENTO -->
            <div>
                <button
                    x-show="tipoDocumento === 'nota_venta'"
                    type="button"
                    disabled
                    title="Acción pendiente de implementación"
                    class="w-full rounded-lg bg-blue-600 px-4 py-3
                           text-sm font-semibold text-white opacity-70"
                >
                    Enviar a caja
                </button>

                <button
                    x-show="tipoDocumento !== 'nota_venta'"
                    x-cloak
                    type="button"
                    disabled
                    title="Acción pendiente de implementación"
                    class="w-full rounded-lg bg-green-600 px-4 py-3
                           text-sm font-semibold text-white opacity-70"
                >
                    Crear documento
                </button>
            </div>
        </aside>
    </div>

    <!-- MODAL AGREGAR / EDITAR -->
    <div
        x-show="modalAbierto"
        x-cloak
        x-transition.opacity
        x-on:keydown.escape.window="cerrarModal()"
        x-on:click.self="cerrarModal()"
        class="fixed inset-0 z-9999 flex items-center justify-center
               bg-black/60 p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="titulo-modal-producto"
    >
        <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl
                    dark:bg-gray-800"
             x-on:click.stop>

            <h3 id="titulo-modal-producto"
                class="mb-5 text-xl font-semibold text-gray-900 dark:text-white"
                x-text="editando === null
                    ? 'Agregar producto o servicio'
                    : 'Editar producto o servicio'">
            </h3>

            <div class="mb-5 rounded-lg bg-gray-50 p-4 dark:bg-gray-900">
                <div class="font-medium" x-text="productoActual?.nombre"></div>

                <div class="mt-1 text-sm text-gray-500">
                    Precio unitario:
                    <span x-text="formato(productoActual?.precio ?? 0)"></span>
                </div>
            </div>

            <form x-on:submit.prevent="guardarItem()" class="space-y-4">

                <div>
                    <label class="mb-1 block text-sm font-medium">
                        Cantidad
                    </label>
                    <input
                        type="number"
                        min="0.001"
                        step="0.001"
                        required
                        x-model.number="cantidad"
                        class="w-full rounded-lg border border-gray-300
                               bg-white px-3 py-2.5
                               dark:border-gray-600 dark:bg-gray-900"
                    >
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium">
                        Tipo de descuento
                    </label>

                    <select
                        x-model="descuentoTipo"
                        class="w-full rounded-lg border border-gray-300
                               bg-white px-3 py-2.5
                               dark:border-gray-600 dark:bg-gray-900"
                    >
                        <option value="porcentaje">Porcentaje (%)</option>
                        <option value="monto">Monto ($)</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium">
                        Descuento
                    </label>

                    <input
                        type="number"
                        min="0"
                        step="any"
                        required
                        x-model.number="descuentoValor"
                        class="w-full rounded-lg border border-gray-300
                               bg-white px-3 py-2.5
                               dark:border-gray-600 dark:bg-gray-900"
                    >
                </div>

                <p x-show="errorModal"
                   x-text="errorModal"
                   class="text-sm text-red-600">
                </p>

                <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-900">
                    <div class="flex justify-between text-sm">
                        <span>Subtotal</span>
                        <span x-text="formato(subtotalModal)"></span>
                    </div>

                    <div class="mt-2 flex justify-between text-sm">
                        <span>Descuento</span>
                        <span x-text="formato(descuentoModal)"></span>
                    </div>

                    <div class="mt-3 flex justify-between border-t pt-3
                                text-lg font-semibold">
                        <span>Total</span>
                        <span x-text="formato(totalModal)"></span>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button
                        type="button"
                        x-on:click="cerrarModal()"
                        class="rounded-lg border border-gray-300 px-4 py-2.5
                               text-sm font-medium dark:border-gray-600"
                    >
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        class="rounded-lg bg-blue-600 px-5 py-2.5
                               text-sm font-medium text-white
                               hover:bg-blue-700"
                        x-text="editando === null ? 'Agregar' : 'Guardar cambios'">
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
