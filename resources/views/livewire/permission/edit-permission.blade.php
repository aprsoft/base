<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-base-content">
                Editar permiso
            </h2>

            <p class="text-sm text-base-content/60">
                Modifica el identificador del permiso.
            </p>
        </div>

        <x-button
            label="Volver"
            icon="o-arrow-left"
            :link="route('permissions.index')"
            no-wire-navigate
            class="btn-ghost btn-sm"
        />
    </div>

    <x-form wire:submit="update" class="!gap-4">
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <x-card shadow class="!p-4">
                <div class="mb-4 flex items-center gap-2">
                    <x-icon name="o-key" class="size-5 text-primary" />

                    <h3 class="font-semibold text-base-content">
                        Datos del permiso
                    </h3>
                </div>

                <div class="space-y-4">
                    <x-input
                        label="Nombre del permiso"
                        wire:model="permission"
                        placeholder="Ej.: users.create"
                        icon="o-identification"
                    />

                    <div class="flex items-center justify-between border-t border-base-300 pt-3">
                        <span class="text-xs font-medium text-base-content/60">
                            Guard de autorización
                        </span>

                        <x-badge
                            value="web"
                            class="badge-neutral badge-soft"
                        />
                    </div>
                </div>
            </x-card>

            <x-card shadow class="!p-4">
                <div class="mb-3 flex items-center gap-2">
                    <x-icon
                        name="o-information-circle"
                        class="size-5 text-primary"
                    />

                    <h3 class="font-semibold text-base-content">
                        Consideraciones de seguridad
                    </h3>
                </div>

                <div class="rounded-lg border border-blue-light-200 bg-blue-light-100 p-3
                            dark:border-blue-light-800 dark:bg-blue-light-950/70">
                    <p class="text-xs leading-5 text-gray-700 dark:text-blue-light-100">
                        Renombrar un permiso puede afectar las comprobaciones
                        de acceso que utilizan su nombre actual.
                        El guard se conserva en <strong>web</strong>.
                    </p>
                </div>

                <p class="mt-3 text-xs text-base-content/60">
                    No se pueden modificar permisos asignados a roles.
                </p>
            </x-card>
        </div>

        @error('permission')
            <p class="text-sm text-error">{{ $message }}</p>
        @enderror

        <x-slot:actions>
            <x-button
                label="Cancelar"
                :link="route('permissions.index')"
                no-wire-navigate
                class="btn-ghost"
            />

            @if ($canDelete)
                <x-button
                    label="Eliminar permiso"
                    icon="o-trash"
                    type="button"
                    wire:click="confirmDelete"
                    spinner="confirmDelete"
                    class="btn-error"
                />
            @endif

            <x-button
                label="Guardar cambios"
                icon="o-check"
                type="submit"
                spinner="update"
                class="btn-primary"
            />
        </x-slot:actions>
    </x-form>
</div>