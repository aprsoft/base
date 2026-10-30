<x-form wire:submit="save">

    @if ($statusMessage)
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-300">
            {{ $statusMessage }}
        </div>
    @endif

    @error('form')
        <p class="mb-4 rounded-lg bg-red-50 p-4 text-sm text-red-600 dark:bg-red-900/20">
            {{ $message }}
        </p>
    @enderror

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 lg:gap-8">

        {{-- DATOS --}}
        <div class="space-y-6 rounded-xl border border-gray-200 p-6 dark:border-gray-700">
            <div class="flex items-center justify-between gap-3">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Datos del usuario
                </h3>

                @if ($user->trashed())
                    <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-700">
                        Suspendido
                    </span>
                @else
                    <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">
                        Activo
                    </span>
                @endif
            </div>

            <x-input label="Nombre" wire:model.live="name" placeholder="Nombre completo" icon="o-user" />
            <x-input label="Email" type="email" wire:model.live="email" placeholder="usuario@aprsoft.cl" icon="o-envelope" />

            <div class="border-t border-gray-200 pt-6 dark:border-gray-700">
                <h3 class="mb-3 text-base font-semibold text-gray-900 dark:text-white">
                    Roles seleccionados
                </h3>

                @if ($selectedRoles->isEmpty())
                    <p class="rounded-lg border border-dashed border-gray-300 p-4 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                        No hay roles seleccionados.
                    </p>
                @else
                    <div class="flex flex-wrap gap-2">
                        @foreach ($selectedRoles as $role)
                            <div
                                wire:key="central-selected-role-{{ $role->id }}"
                                class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                            >
                                <span>{{ $role->name }}</span>

                                @if ($roles->contains('id', $role->id))
                                    <button
                                        type="button"
                                        wire:click="removeRole({{ $role->id }})"
                                        aria-label="Quitar rol {{ $role->name }}"
                                        class="text-gray-400 transition hover:text-red-500"
                                    >
                                        &times;
                                    </button>
                                @else
                                    <span class="text-xs text-amber-600 dark:text-amber-400">
                                        Protegido
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- ROLES Y ESTADO --}}
        <div class="space-y-6 rounded-xl border border-gray-200 p-6 dark:border-gray-700">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Roles disponibles
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Selecciona uno o más roles.
                </p>
            </div>

            <div class="max-h-80 space-y-2 overflow-y-auto rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                @forelse ($roles as $role)
                    <label
                        wire:key="central-available-role-{{ $role->id }}"
                        class="flex cursor-pointer items-center gap-3 rounded-lg px-3 py-2 transition hover:bg-gray-50 dark:hover:bg-gray-800"
                    >
                        <input
                            type="checkbox"
                            value="{{ $role->id }}"
                            wire:model.live="roleIds"
                            class="h-4 w-4 rounded"
                        />
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">
                            {{ $role->name }}
                        </span>
                    </label>
                @empty
                    <p class="py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                        No existen roles disponibles.
                    </p>
                @endforelse
            </div>

            @error('roleIds')
                <p class="text-sm text-red-500">{{ $message }}</p>
            @enderror

            <div class="border-t border-gray-200 pt-6 dark:border-gray-700">
                <h3 class="mb-3 text-base font-semibold text-gray-900 dark:text-white">
                    Estado de la cuenta
                </h3>
                <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
                    Puedes suspender temporalmente el acceso del usuario
                    y reactivarlo cuando sea necesario.
                    Sus datos y roles se conservarán.
                </p>

                @can('central.users.destroy')
                    @if ($user->trashed())
                        <button
                            type="button"
                            wire:click="reactivate"
                            wire:confirm="¿Deseas reactivar este usuario?"
                            wire:loading.attr="disabled"
                            wire:target="reactivate"
                            class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700 disabled:opacity-50"
                        >
                            Reactivar usuario
                        </button>
                    @elseif (
                        (int) $user->id !== (int) auth('web')->id()
                        && ! $user->hasRole('SuperAdmin', 'web')
                    )
                        <button
                            type="button"
                            wire:click="suspend"
                            wire:confirm="¿Confirmas que deseas suspender este usuario?"
                            wire:loading.attr="disabled"
                            wire:target="suspend"
                            class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-600 disabled:opacity-50"
                        >
                            Suspender usuario
                        </button>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Esta cuenta está protegida contra suspensión.
                        </p>
                    @endif
                @endcan
            </div>
        </div>
    </div>

    <x-slot:actions>
        <x-button
            label="Cancelar"
            link="{{ route('central.users.index') }}"
            class="btn-ghost"
        />
        <x-button
            label="Guardar cambios"
            class="btn-primary"
            type="submit"
            spinner="save"
        />
    </x-slot:actions>
</x-form>
