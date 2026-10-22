<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-base-content">Nuevo rol</h2>
            <p class="text-sm text-base-content/60">Crea un perfil y selecciona sus permisos.</p>
        </div>
        <x-button label="Volver" icon="o-arrow-left" :link="route('roles.index')"
                  no-wire-navigate class="btn-ghost btn-sm" />
    </div>

    <x-form wire:submit="save" class="!gap-4">
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <x-card shadow class="!p-4">
                <div class="mb-4 flex items-center gap-2">
                    <x-icon name="o-shield-check" class="size-5 text-primary" />
                    <h3 class="font-semibold text-base-content">Datos del rol</h3>
                </div>
                <div class="space-y-3">
                    <x-input label="Nombre del rol" wire:model="role"
                             placeholder="Ej.: Supervisor" icon="o-identification" />

                    <div class="flex items-center justify-between border-t border-base-300 pt-3">
                        <span class="text-xs font-medium text-base-content/60">Guard de autorización</span>
                        <x-badge value="web" class="badge-neutral badge-soft" />
                    </div>

                    <div class="flex items-start gap-2 rounded-lg border border-blue-light-200 bg-blue-light-100 p-3
                                dark:border-blue-light-800 dark:bg-blue-light-950/70">
                        <x-icon name="o-information-circle" class="mt-0.5 size-5 shrink-0 text-blue-light-700 dark:text-blue-light-300" />
                        <p class="text-xs leading-5 text-gray-700 dark:text-blue-light-100">
                            Los permisos determinan las acciones autorizadas para quienes tengan este rol.
                            Los cambios se aplicarán al guardar.
                        </p>
                    </div>

                    <div class="border-t border-base-300 pt-3">
                        <div class="mb-2 flex items-center justify-between gap-2">
                            <span class="text-sm font-medium text-base-content">Permisos seleccionados</span>
                            <x-badge :value="$selectedPermissions->count()" class="badge-primary badge-soft" />
                        </div>
                        @if ($selectedPermissions->isEmpty())
                            <p class="text-xs text-base-content/60">Todavía no hay permisos seleccionados.</p>
                        @else
                            <div class="flex max-h-32 flex-wrap content-start gap-1.5 overflow-y-auto">
                                @foreach ($selectedPermissions as $permission)
                                    <x-badge wire:key="selected-permission-{{ $permission->id }}"
                                             :value="$permission->name" class="badge-primary badge-soft" />
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </x-card>

            <x-card shadow class="!p-4">
                <div class="mb-3 flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <x-icon name="o-key" class="size-5 text-primary" />
                        <h3 class="font-semibold text-base-content">Permisos disponibles</h3>
                    </div>
                    <x-badge :value="$permissions->count() . ' disponibles'" class="badge-neutral badge-soft" />
                </div>
                <div class="max-h-60 space-y-1 overflow-y-auto rounded-lg border border-base-300 p-2">
                    @forelse ($permissions as $permission)
                        <div wire:key="role-permission-{{ $permission->id }}"
                             class="rounded-md px-2 py-1 hover:bg-base-200">
                            <x-checkbox :label="$permission->name"
                                        value="{{ $permission->id }}"
                                        wire:model.number.live="permissionIds"
                                        class="checkbox-primary checkbox-sm"
                                        omit-error />
                        </div>
                    @empty
                        <p class="py-4 text-center text-sm text-base-content/60">
                            No existen permisos disponibles.
                        </p>
                    @endforelse
                </div>
                @error('permissionIds')
                    <p class="mt-2 text-xs text-error">{{ $message }}</p>
                @enderror
                @foreach ($errors->get('permissionIds.*') as $messages)
                    @foreach ((array) $messages as $message)
                        <p class="mt-1 text-xs text-error">{{ $message }}</p>
                    @endforeach
                @endforeach
            </x-card>
        </div>
        <x-slot:actions>
            <x-button label="Cancelar" :link="route('roles.index')"
                      no-wire-navigate class="btn-ghost" />
            <x-button label="Crear rol" icon="o-plus"
                      type="submit" spinner="save" class="btn-primary" />
        </x-slot:actions>
    </x-form>
</div>
