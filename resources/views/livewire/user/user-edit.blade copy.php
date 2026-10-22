
<div class="space-y-6">

        {{-- Mensajes --}}

        {{-- @if (session('success'))
            <x-ui.alert
                variant="success"
                :message="session('success')"
            />
        @endif

        @if (session('warning'))
            <x-ui.alert
                variant="warning"
                :message="session('warning')"
            />
        @endif --}}

        {{-- Datos del usuario --}}

    <div class="rounded-xl border border-gray-200
                bg-white p-6 dark:border-gray-800
                dark:bg-gray-900">

        <form wire:submit="save" class="space-y-6">

            <div>
                <label
                    for="user-name"
                    class="mb-1.5 block text-sm font-medium
                           text-gray-700 dark:text-gray-400"
                >
                    Nombre
                </label>

                <input
                    id="user-name"
                    type="text"
                    wire:model="name"
                    autocomplete="name"
                    maxlength="255"
                 
                    class="h-11 w-full rounded-lg border
                           border-gray-300 bg-transparent
                           px-4 text-sm text-gray-800
                           focus:border-brand-300
                           focus:outline-none
                           dark:border-gray-700 dark:text-white"
                />

                @error('name')
                    <p class="mt-1 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div>
                <label
                    for="user-email"
                    class="mb-1.5 block text-sm font-medium
                           text-gray-700 dark:text-gray-400"
                >
                    Correo electrónico
                </label>

                <input
                    id="user-email"
                    type="email"
                    wire:model="email"
                    autocomplete="email"
                    maxlength="255"
              
                    class="h-11 w-full rounded-lg border
                           border-gray-300 bg-transparent
                           px-4 text-sm text-gray-800
                           focus:border-brand-300
                           focus:outline-none
                           dark:border-gray-700 dark:text-white"
                />

                @error('email')
                    <p class="mt-1 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Guardar --}}

            <div class="flex justify-end">
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="save"
                    class="inline-flex min-w-40 items-center
                           justify-center rounded-lg
                           bg-brand-500 px-4 py-2.5
                           text-sm font-medium text-white
                           shadow-theme-xs transition
                           hover:bg-brand-600
                           disabled:cursor-not-allowed
                           disabled:opacity-60"
                >
                    <span
                        wire:loading.remove
                        wire:target="save"
                    >
                        Guardar cambios
                    </span>

                    <span
                        wire:loading.inline-flex
                        wire:target="save"
                        class="items-center gap-2"
                    >
                        <svg
                            class="h-4 w-4 animate-spin"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"
                            ></circle>
                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                            ></path>
                        </svg>

                        Guardando cambios...
                    </span>
                </button>
            </div>

        </form>
    </div>

    {{-- Restablecimiento de contraseña --}}

    <div class="rounded-xl border border-gray-200
                bg-white p-6 dark:border-gray-800
                dark:bg-gray-900">

        <h3 class="mb-2 text-lg font-semibold
                   text-gray-800 dark:text-white">
            Restablecer contraseña
        </h3>

        <p class="mb-5 text-sm text-gray-500
                  dark:text-gray-400">
            Se enviará un enlace al correo electrónico
            registrado para que el usuario pueda
            establecer una nueva contraseña.
        </p>

        <button
            type="button"
            wire:click="confirmResetPassword"
            wire:loading.attr="disabled"
            wire:target="confirmResetPassword,resetPassword"
            class="inline-flex items-center justify-center
                   rounded-lg border border-gray-300
                   bg-white px-4 py-2.5
                   text-sm font-medium text-gray-700
                   shadow-theme-xs transition
                   hover:bg-gray-50
                   disabled:cursor-not-allowed
                   disabled:opacity-60
                   dark:border-gray-700 dark:bg-gray-800
                   dark:text-gray-200 dark:hover:bg-gray-700"
        >
            <span
                wire:loading.remove
                wire:target="confirmResetPassword,resetPassword"
            >
                Enviar enlace de contraseña
            </span>

            <span
                wire:loading.inline-flex
                wire:target="confirmResetPassword,resetPassword"
                class="items-center gap-2"
            >
                Procesando...
            </span>
        </button>

    </div>
</div>
