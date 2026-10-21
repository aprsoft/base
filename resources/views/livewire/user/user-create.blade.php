
<div class="rounded-xl border border-gray-200 bg-white p-6
            dark:border-gray-800 dark:bg-gray-900">

    @if (session('success'))
        <div class="mb-6">
            <x-ui.alert
                variant="success"
                :message="session('success')"
            />
        </div>
    @endif

    @if (session('warning'))
        <div class="mb-6">
            <x-ui.alert
                variant="warning"
                :message="session('warning')"
            />
        </div>
    @endif

    <form wire:submit="save" class="space-y-6">

        <div>
            <label
                for="name"
                class="mb-1.5 block text-sm font-medium
                       text-gray-700 dark:text-gray-400"
            >
                Nombre
            </label>

            <input
                id="name"
                type="text"
                wire:model="name"
                autocomplete="name"
                placeholder="Nombre completo"
                required
                maxlength="255"
                class="h-11 w-full rounded-lg border
                       border-gray-300 bg-transparent
                       px-4 py-2.5 text-sm text-gray-800
                       focus:border-brand-300
                       focus:outline-none
                       dark:border-gray-700
                       dark:text-white"
            />

            @error('name')
                <p class="mt-1 text-sm text-red-600">
                    {{ $message }}
                </p>
            @enderror
        </div>

        <div>
            <label
                for="email"
                class="mb-1.5 block text-sm font-medium
                       text-gray-700 dark:text-gray-400"
            >
                Correo electrónico
            </label>

            <input
                id="email"
                type="email"
                wire:model="email"
                autocomplete="email"
                placeholder="usuario@ejemplo.cl"
                required
                maxlength="255"
                class="h-11 w-full rounded-lg border
                       border-gray-300 bg-transparent
                       px-4 py-2.5 text-sm text-gray-800
                       focus:border-brand-300
                       focus:outline-none
                       dark:border-gray-700
                       dark:text-white"
            />

            @error('email')
                <p class="mt-1 text-sm text-red-600">
                    {{ $message }}
                </p>
            @enderror
        </div>

        <x-ui.alert
            variant="info"
            title="Acceso al sistema"
            message="Al guardar, se enviará un correo electrónico al nuevo usuario con un enlace seguro para establecer su contraseña de ingreso al sistema."
        />

        <div class="flex justify-end">
            <x-ui.button
                type="submit"
                variant="primary"
                wire:loading.attr="disabled"
                wire:target="save"
            >
                <span wire:loading.remove wire:target="save">
                    Guardar usuario
                </span>

                <span wire:loading wire:target="save">
                    Guardando...
                </span>
            </x-ui.button>
        </div>

    </form>
</div>
