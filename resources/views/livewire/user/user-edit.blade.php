<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-base-content">
                {{ $isSuspended ? 'Restaurar usuario' : 'Editar usuario' }}
            </h2>
            <p class="text-sm text-base-content/60">
                {{ $isSuspended
                    ? 'La cuenta está suspendida. Puedes restaurarla si tienes autorización.'
                    : 'Actualiza los datos, roles y acceso de la cuenta.' }}
            </p>
        </div>

        <x-button
            label="Volver"
            icon="o-arrow-left"
            :link="route('users.index')"
            no-wire-navigate
            class="btn-ghost btn-sm"
        />
    </div>

    <x-form wire:submit="confirmSave" class="!gap-4">
        @error('form')
            <div class="rounded-lg bg-error/10 p-3 text-sm text-error">
                {{ $message }}
            </div>
        @enderror

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <x-card id="edit-user-data" shadow class="!p-4">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <x-icon name="o-user-circle" class="size-5 text-primary" />
                        <h3 class="font-semibold text-base-content">Datos del usuario</h3>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        @if ($canSuspend)
                            <x-button
                                label="Suspender"
                                icon="o-no-symbol"
                                type="button"
                                wire:click="confirmSuspend"
                                spinner="confirmSuspend"
                                class="btn-sm !border-amber-600 !bg-amber-600 !text-white hover:!bg-amber-700"
                            />
                        @endif

                        @if ($canRestore)
                            <x-button
                                label="Restaurar"
                                icon="o-arrow-path"
                                type="button"
                                wire:click="confirmRestore"
                                spinner="confirmRestore"
                                class="btn-sm !border-emerald-600 !bg-emerald-600 !text-white hover:!bg-emerald-700"
                            />
                        @endif

                        {{-- Eliminar definitivamente continúa deshabilitado en UserPolicy::delete().
                             Solo habilitarlo después de auditar las relaciones y definir el borrado físico. --}}
                    </div>
                </div>

                @if ($canUpdate)
                    <div class="space-y-3">
                        <x-input
                            label="Nombre completo"
                            wire:model.live="name"
                            placeholder="Nombre completo"
                            icon="o-user"
                        />

                        <x-input
                            label="Correo electrónico"
                            type="email"
                            wire:model.live="email"
                            placeholder="usuario@aprsoft.cl"
                            icon="o-envelope"
                        />

                        <div class="flex items-start gap-2 rounded-lg border border-blue-light-200 bg-blue-light-100 p-3 dark:border-blue-light-800 dark:bg-blue-light-950/70">
                            <x-icon
                                name="o-information-circle"
                                class="mt-0.5 size-5 shrink-0 text-blue-light-700 dark:text-blue-light-300"
                            />
                            <div class="space-y-1">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                    Cambio de correo electrónico
                                </p>
                                <p class="text-xs leading-5 text-gray-700 dark:text-blue-light-100">
                                    Si modificas el correo electrónico y guardas los cambios,
                                    se solicitará un enlace a la dirección actualizada para
                                    que el usuario pueda establecer una nueva contraseña.
                                </p>
                                <p class="text-xs font-semibold text-blue-light-700 dark:text-blue-light-300">
                                    Vigencia del enlace: 60 minutos.
                                </p>
                            </div>
                        </div>

                        <div class="border-t border-base-300 pt-3">
                            <div class="mb-2 flex items-center gap-2">
                                <x-icon name="o-key" class="size-5 text-primary" />
                                <h3 class="text-sm font-semibold text-base-content">
                                    Restablecer contraseña
                                </h3>
                            </div>

                            <p class="mb-3 text-xs leading-5 text-base-content/65">
                                Puedes solicitar manualmente un enlace de restablecimiento
                                al correo registrado. Si modificaste el correo,
                                primero guarda los cambios.
                            </p>

                            <x-button
                                label="Enviar enlace de contraseña"
                                icon="o-envelope"
                                type="button"
                                wire:click="confirmResetPassword"
                                spinner="confirmResetPassword"
                                class="btn-sm !border-gray-600 !bg-gray-600 !text-white hover:!bg-gray-700 dark:!border-gray-600 dark:!bg-gray-600 dark:hover:!bg-gray-500"
                            />
                        </div>
                    </div>
                @else
                    <div class="space-y-4 rounded-lg border border-base-300 bg-base-200/50 p-4">
                        <div>
                            <p class="text-xs text-base-content/60">Nombre completo</p>
                            <p class="font-medium text-base-content">{{ $user->name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-base-content/60">Correo electrónico</p>
                            <p class="break-all font-medium text-base-content">{{ $user->email }}</p>
                        </div>
                        @if ($isSuspended)
                            <p class="text-sm font-medium text-red-600">
                                Cuenta suspendida. Sus datos no pueden modificarse hasta restaurarla.
                            </p>
                        @else
                            <p class="text-sm text-base-content/60">
                                No tienes autorización para modificar los datos de esta cuenta.
                            </p>
                        @endif
                    </div>
                @endif
            </x-card>

            <x-card id="edit-user-roles" shadow class="!p-4">
                @if ($isSuperAdminTarget)
                    <x-user.super-admin-notice />
                @elseif ($canUpdate)
                    <div class="mb-3 flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <x-icon name="o-shield-check" class="size-5 text-primary" />
                            <h3 class="font-semibold text-base-content">Roles disponibles</h3>
                        </div>
                        <x-badge
                            :value="$selectedRoles->count() . ' seleccionados'"
                            class="badge-primary badge-soft"
                        />
                    </div>

                    <div class="max-h-44 space-y-1 overflow-y-auto rounded-lg border border-base-300 p-2">
                        @forelse ($roles as $role)
                            <div wire:key="edit-available-role-{{ $role->id }}" class="rounded-md px-2 py-1 hover:bg-base-200">
                                <x-checkbox
                                    :label="$role->name"
                                    value="{{ $role->id }}"
                                    wire:model.live="roleIds"
                                    class="checkbox-primary checkbox-sm"
                                    omit-error
                                />
                            </div>
                        @empty
                            <p class="py-4 text-center text-sm text-base-content/60">
                                No existen roles disponibles.
                            </p>
                        @endforelse
                    </div>

                    @error('roleIds')
                        <p class="mt-2 text-xs text-error">{{ $message }}</p>
                    @enderror

                    @foreach ($errors->get('roleIds.*') as $message)
                        <p class="mt-1 text-xs text-error">{{ $message }}</p>
                    @endforeach

                    <div class="mt-3 border-t border-base-300 pt-3">
                        <p class="mb-2 text-sm font-medium text-base-content">Roles seleccionados</p>

                        @if ($selectedRoles->isEmpty())
                            <p class="text-xs text-base-content/50">Ningún rol seleccionado.</p>
                        @else
                            <div class="flex flex-wrap gap-2">
                                @foreach ($selectedRoles as $role)
                                    <div wire:key="edit-selected-role-{{ $role->id }}" class="inline-flex items-center gap-1 rounded-lg bg-base-200 px-2 py-1">
                                        <x-badge :value="$role->name" class="badge-primary badge-soft" />
                                        <x-button
                                            icon="o-x-mark"
                                            type="button"
                                            wire:click="confirmRemoveRole({{ $role->id }})"
                                            aria-label="Quitar rol {{ $role->name }}"
                                            class="btn-ghost btn-circle btn-xs text-error"
                                        />
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @else
                    <div class="mb-3 flex items-center gap-2">
                        <x-icon name="o-shield-check" class="size-5 text-primary" />
                        <h3 class="font-semibold text-base-content">Roles asignados</h3>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        @forelse ($user->roles->where('guard_name', 'web') as $role)
                            <x-badge :value="$role->name" class="badge-primary badge-soft" />
                        @empty
                            <p class="text-sm text-base-content/60">No hay roles asignados.</p>
                        @endforelse
                    </div>
                @endif
            </x-card>
        </div>

        <x-slot:actions>
            @if ($canUpdate)
                <x-button
                    label="Guardar cambios"
                    icon="o-check"
                    type="submit"
                    spinner="confirmSave"
                    class="btn-primary"
                />
            @endif
        </x-slot:actions>
    </x-form>
</div>