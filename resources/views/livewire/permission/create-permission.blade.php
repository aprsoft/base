<div class="space-y-6">

    <form wire:submit="save" class="space-y-6">

        <div
            class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm
                   dark:border-gray-800 dark:bg-white/[0.03]"
        >
            <div class="max-w-2xl">

                <div class="mb-4">
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">
                        Nuevo permiso
                    </h3>

                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Ingresa el nombre que identificará al permiso.
                    </p>
                </div>

                <x-input
                    label="Nombre del permiso"
                    wire:model="permission"
                    placeholder="Ej: users.create"
                />

                @error('permission')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                        {{ $message }}
                    </p>
                @enderror

                <div class="mt-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Guard
                    </p>

                    <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-white/90">
                        web
                    </p>
                </div>

            </div>
        </div>

        <div
            class="flex flex-col-reverse gap-3
                   sm:flex-row sm:justify-end"
        >
            {{-- Cancelar --}}
            <a
                href="{{ route('central.permissions.index') }}"
                wire:loading.class="pointer-events-none opacity-50"
                class="inline-flex items-center justify-center rounded-lg
                       border border-gray-300 bg-white px-4 py-2.5
                       text-sm font-medium text-gray-700 shadow-sm
                       transition hover:bg-gray-50
                       dark:border-gray-700 dark:bg-gray-800
                       dark:text-gray-300 dark:hover:bg-gray-700"
            >
                Cancelar
            </a>

            {{-- Guardar y crear otro --}}
            <button
                type="button"
                wire:click="saveAndCreateAnother"
                wire:loading.attr="disabled"
                class="inline-flex min-w-44 items-center justify-center
                       rounded-lg border border-blue-600 bg-white
                       px-4 py-2.5 text-sm font-medium text-blue-600
                       shadow-sm transition
                       hover:bg-blue-50
                       disabled:cursor-not-allowed
                       disabled:opacity-60
                       dark:bg-gray-900
                       dark:hover:bg-blue-500/10"
            >
                <span
                    wire:loading.remove
                    wire:target="saveAndCreateAnother"
                >
                    Guardar Permiso y crear otro
                </span>

                <span
                    wire:loading
                    wire:target="saveAndCreateAnother"
                >
                    Guardando...
                </span>
            </button>

            {{-- Guardar --}}
            <button
                type="submit"
                wire:loading.attr="disabled"
                class="inline-flex min-w-36 items-center justify-center
                       rounded-lg bg-blue-600 px-4 py-2.5
                       text-sm font-medium text-white shadow-sm
                       transition hover:bg-blue-700
                       disabled:cursor-not-allowed
                       disabled:opacity-60"
            >
                <span
                    wire:loading.remove
                    wire:target="save"
                >
                    Guardar permiso
                </span>

                <span
                    wire:loading
                    wire:target="save"
                >
                    Guardando...
                </span>
            </button>
        </div>

    </form>

</div>