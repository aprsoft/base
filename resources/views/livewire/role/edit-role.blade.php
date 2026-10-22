<div class="space-y-6">

    <form wire:submit="update" class="space-y-6">

        {{-- Datos principales --}}
        <div
            class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm
                   dark:border-gray-800 dark:bg-white/[0.03]"
        >
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

                {{-- Nombre del rol --}}
                <div>
                    <div class="mb-4">
                        <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">
                            Editar rol
                        </h3>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Modifica el nombre que identifica al rol.
                        </p>
                    </div>

                    <x-input
                        label="Nombre del rol"
                        wire:model="role"
                        placeholder="Ej: Supervisor"
                    />

                    @error('role')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Permisos seleccionados --}}
                <div>
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">
                                Permisos seleccionados
                            </h3>

                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Los permisos marcados aparecerán aquí.
                            </p>
                        </div>

                        <span
                            class="inline-flex min-w-8 items-center justify-center rounded-full
                                   bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700
                                   dark:bg-gray-800 dark:text-gray-300"
                        >
                            {{ $selectedPermissions->count() }}
                        </span>
                    </div>

                    <div
                        class="min-h-32 rounded-xl border border-dashed border-gray-300
                               bg-gray-50 p-4
                               dark:border-gray-700 dark:bg-gray-900/40"
                    >
                        @if ($selectedPermissions->isNotEmpty())
                            <div class="flex flex-wrap gap-2">
                                @foreach ($selectedPermissions as $permission)
                                    <span
                                        wire:key="selected-permission-{{ $permission->id }}"
                                        class="inline-flex items-center rounded-lg
                                               bg-blue-50 px-3 py-1.5
                                               text-sm font-medium text-blue-700
                                               ring-1 ring-inset ring-blue-700/10
                                               dark:bg-blue-500/10 dark:text-blue-400"
                                    >
                                        {{ $permission->name }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <div
                                class="flex min-h-24 items-center justify-center
                                       text-center text-sm text-gray-500
                                       dark:text-gray-400"
                            >
                                Este rol no tiene permisos seleccionados.
                            </div>
                        @endif
                    </div>

                    @error('permissionIds')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

            </div>
        </div>

        {{-- Tabla de permisos --}}
        <div
            class="overflow-hidden rounded-2xl border border-gray-200
                   bg-white shadow-sm
                   dark:border-gray-800 dark:bg-white/[0.03]"
        >
            <div
                class="flex items-center justify-between border-b border-gray-200
                       px-5 py-4 dark:border-gray-800"
            >
                <div>
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">
                        Permisos
                    </h3>

                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Marca o desmarca los permisos que tendrá este rol.
                    </p>
                </div>

                <span
                    class="rounded-full bg-gray-100 px-3 py-1
                           text-xs font-semibold text-gray-700
                           dark:bg-gray-800 dark:text-gray-300"
                >
                    {{ $permissions->count() }} disponibles
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">

                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th
                                class="w-16 px-5 py-3 text-left text-xs
                                       font-semibold uppercase tracking-wide
                                       text-gray-500 dark:text-gray-400"
                            >
                                Sel.
                            </th>

                            <th
                                class="px-5 py-3 text-left text-xs
                                       font-semibold uppercase tracking-wide
                                       text-gray-500 dark:text-gray-400"
                            >
                                Permiso
                            </th>

                            <th
                                class="w-32 px-5 py-3 text-left text-xs
                                       font-semibold uppercase tracking-wide
                                       text-gray-500 dark:text-gray-400"
                            >
                                Estado
                            </th>
                        </tr>
                    </thead>

                    <tbody
                        class="divide-y divide-gray-100 bg-white
                               dark:divide-gray-800 dark:bg-transparent"
                    >
                        @forelse ($permissions as $permission)

                            @php
                                $isSelected = in_array(
                                    (int) $permission->id,
                                    $permissionIds,
                                    true
                                );
                            @endphp

                            <tr wire:key="permission-row-{{ $permission->id }}">

                                <td class="px-5 py-3">
                                    <input
                                        type="checkbox"
                                        value="{{ $permission->id }}"
                                        wire:model.number.live="permissionIds"
                                        class="h-4 w-4 rounded border-gray-300
                                               text-blue-600 focus:ring-blue-500
                                               dark:border-gray-600
                                               dark:bg-gray-800"
                                    >
                                </td>

                                <td
                                    class="px-5 py-3 text-sm font-medium
                                           text-gray-700 dark:text-gray-300"
                                >
                                    {{ $permission->name }}
                                </td>

                                <td class="px-5 py-3">
                                    @if ($isSelected)
                                        <span
                                            class="inline-flex rounded-full
                                                   bg-green-50 px-2.5 py-1
                                                   text-xs font-medium text-green-700
                                                   dark:bg-green-500/10
                                                   dark:text-green-400"
                                        >
                                            Asignado
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex rounded-full
                                                   bg-gray-100 px-2.5 py-1
                                                   text-xs font-medium text-gray-600
                                                   dark:bg-gray-800
                                                   dark:text-gray-400"
                                        >
                                            Disponible
                                        </span>
                                    @endif
                                </td>

                            </tr>

                        @empty
                            <tr>
                                <td
                                    colspan="3"
                                    class="px-5 py-8 text-center
                                           text-sm text-gray-500
                                           dark:text-gray-400"
                                >
                                    No existen permisos disponibles.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>
        </div>

        {{-- Acciones --}}
        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

            <a
                href="{{ route('central.roles.index') }}"
                class="inline-flex items-center justify-center rounded-lg
                       border border-gray-300 bg-white px-4 py-2.5
                       text-sm font-medium text-gray-700 shadow-sm
                       transition hover:bg-gray-50
                       dark:border-gray-700 dark:bg-gray-800
                       dark:text-gray-300 dark:hover:bg-gray-700"
            >
                Cancelar
            </a>

            <button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="update"
                class="inline-flex items-center justify-center rounded-lg
                       bg-blue-600 px-4 py-2.5
                       text-sm font-medium text-white shadow-sm
                       transition hover:bg-blue-700
                       disabled:cursor-not-allowed disabled:opacity-60"
            >
                <span wire:loading.remove wire:target="update">
                    Actualizar rol
                </span>

                <span wire:loading wire:target="update">
                    Actualizando...
                </span>
            </button>

        </div>

    </form>

</div>