<div
    x-data="{
        open: false,

        payload: {
            id: null,
            name: '',
            confirmEvent: null,
            title: 'Confirmar acción',
            message: '¿Está seguro de que desea continuar?',
            warning: '',
            confirmLabel: 'Confirmar',
        },

        show(event) {
            this.payload = {
                id: event.detail?.id ?? null,
                name: event.detail?.name ?? '',
                confirmEvent: event.detail?.confirmEvent ?? null,
                title: event.detail?.title ?? 'Confirmar acción',
                message: event.detail?.message
                    ?? '¿Está seguro de que desea continuar?',
                warning: event.detail?.warning ?? '',
                confirmLabel: event.detail?.confirmLabel ?? 'Confirmar',
            };

            if (
                this.payload.id === null ||
                ! this.payload.confirmEvent
            ) {
                return;
            }

            this.open = true;
            document.body.style.overflow = 'hidden';
        },

        close() {
            this.open = false;
            document.body.style.overflow = '';

            this.payload = {
                id: null,
                name: '',
                confirmEvent: null,
                title: 'Confirmar acción',
                message: '¿Está seguro de que desea continuar?',
                warning: '',
                confirmLabel: 'Confirmar',
            };
        },

        confirm() {
            if (
                this.payload.id === null ||
                ! this.payload.confirmEvent
            ) {
                return;
            }

            Livewire.dispatch(
                this.payload.confirmEvent,
                {
                    id: this.payload.id,
                }
            );

            this.close();
        },
    }"
    x-on:open-confirm-modal.window="show($event)"
    x-on:keydown.escape.window="if (open) close()"
>
    <div
        x-show="open"
        x-cloak
        class="fixed inset-0 z-[99999] flex items-center justify-center p-4 sm:p-6"
        role="dialog"
        aria-modal="true"
        x-bind:aria-label="payload.title"
    >
        {{-- Backdrop --}}
        <div
            x-show="open"
            x-on:click="close()"
            class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        ></div>

        {{-- Modal --}}
        <div
            x-show="open"
            x-on:click.stop
            class="relative z-10 w-full max-w-md overflow-hidden
                   rounded-2xl bg-white shadow-2xl
                   dark:bg-gray-900"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-2"
        >
            <div class="px-6 pb-6 pt-7 sm:px-8 sm:pb-8">

                {{-- Icono --}}
                <div
                    class="mx-auto flex h-14 w-14 items-center justify-center
                           rounded-full bg-red-50 text-red-600
                           dark:bg-red-500/10 dark:text-red-400"
                >
                    <svg
                        class="h-7 w-7"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 9v4m0 4h.01M10.29 3.86 1.82 18
                               a2 2 0 0 0 1.71 3h16.94
                               a2 2 0 0 0 1.71-3L13.71 3.86
                               a2 2 0 0 0-3.42 0Z"
                        />
                    </svg>
                </div>

                {{-- Título --}}
                <h3
                    x-text="payload.title"
                    class="mt-5 text-center text-xl font-semibold
                           text-gray-900 dark:text-white"
                ></h3>

                {{-- Mensaje --}}
                <p
                    x-text="payload.message"
                    class="mt-2 text-center text-sm leading-6
                           text-gray-500 dark:text-gray-400"
                ></p>

                {{-- Registro --}}
                <div
                    x-show="payload.name"
                    class="mt-5 rounded-xl border border-gray-200
                           bg-gray-50 px-4 py-3 text-center
                           dark:border-gray-800 dark:bg-gray-800/50"
                >
                    <span
                        x-text="payload.name"
                        class="break-all text-sm font-semibold
                               text-gray-800 dark:text-gray-200"
                    ></span>
                </div>

                {{-- Advertencia --}}
                <p
                    x-show="payload.warning"
                    x-text="payload.warning"
                    class="mt-4 text-center text-sm font-medium
                           text-red-600 dark:text-red-400"
                ></p>

                {{-- Botones --}}
                <div
                    class="mt-7 flex flex-col-reverse gap-3
                           sm:flex-row sm:justify-center"
                >
                    <button
                        type="button"
                        x-on:click="close()"
                        class="inline-flex min-w-28 items-center justify-center
                               rounded-lg border border-gray-300 bg-white
                               px-4 py-2.5 text-sm font-medium text-gray-700
                               shadow-sm transition
                               hover:bg-gray-50
                               focus:outline-none focus:ring-2
                               focus:ring-gray-300 focus:ring-offset-2
                               dark:border-gray-700 dark:bg-gray-800
                               dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        Cancelar
                    </button>

                    <button
                        type="button"
                        x-on:click="confirm()"
                        x-bind:disabled="
                            payload.id === null ||
                            ! payload.confirmEvent
                        "
                        class="inline-flex min-w-28 items-center justify-center
                               rounded-lg bg-red-600 px-4 py-2.5
                               text-sm font-medium text-white
                               shadow-sm transition
                               hover:bg-red-700
                               focus:outline-none focus:ring-2
                               focus:ring-red-500 focus:ring-offset-2
                               disabled:cursor-not-allowed
                               disabled:opacity-50
                               dark:focus:ring-offset-gray-900"
                        x-text="payload.confirmLabel"
                    ></button>
                </div>
            </div>
        </div>
    </div>
</div>