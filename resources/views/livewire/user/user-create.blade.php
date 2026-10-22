<x-form wire:submit="save">
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 lg:gap-8">
        <div class="space-y-6 rounded-xl border border-gray-200 p-6 dark:border-gray-700">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Datos del usuario
                </h3>
            </div>

            <x-input
                label="Nombre"
                wire:model.live="name"
                placeholder="Nombre completo"
                icon="o-user"
            />

            <x-input
                label="Email"
                type="email"
                wire:model.live="email"
                placeholder="usuario@aprsoft.cl"
                icon="o-envelope"
            />

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
                                <button
                                    type="button"
                                    wire:click="removeRole({{ $role->id }})"
                                    aria-label="Quitar rol {{ $role->name }}"
                                    class="text-gray-400 transition hover:text-red-500"
                                >
                                    &times;
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="space-y-6 rounded-xl border border-gray-200 p-6 dark:border-gray-700">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Roles disponibles
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Selecciona uno o más roles. Puedes desmarcarlos o quitarlos desde la lista de roles seleccionados.
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
                        No existen roles disponibles
                    </p>
                @endforelse
            </div>

            @error('roleIds')
                <p class="text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>
    </div>
     
    <x-slot:actions>
        <x-button label="Guardar" class="btn-primary" type="submit" spinner="save" />
    </x-slot:actions>
</x-form>
