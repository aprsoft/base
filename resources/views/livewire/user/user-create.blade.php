
<div class="space-y-4">

    {{-- Encabezado compacto --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-base-content">
                Nuevo usuario
            </h2>
            <p class="text-sm text-base-content/60">
                Registra una cuenta y asigna sus roles.
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

            {{-- Datos del usuario --}}
            <x-card shadow class="!p-4">

                <div class="mb-4 flex items-center gap-2">
                    <x-icon name="o-user-circle"
                            class="size-5 text-primary" />

                    <h3 class="font-semibold text-base-content">
                        Datos del usuario
                    </h3>
                </div>

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

                    <div class="flex items-start gap-2 rounded-lg
                                bg-info/10 p-3">
                        <x-icon
                            name="o-information-circle"
                            class="mt-0.5 size-4 shrink-0 text-info"
                        />

                        <p class="text-xs leading-5 text-base-content/70">
                            Se solicitará un enlace por correo para establecer
                            la contraseña. Vigencia: 60 minutos.
                        </p>
                    </div>

                </div>
            </x-card>

            {{-- Administración de roles --}}
            <x-card shadow class="!p-4">

                <div class="mb-3 flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <x-icon name="o-shield-check"
                                class="size-5 text-primary" />

                        <h3 class="font-semibold text-base-content">
                            Roles disponibles
                        </h3>
                    </div>

                    <x-badge
                        :value="$selectedRoles->count() . ' seleccionados'"
                        class="badge-primary badge-soft"
                    />
                </div>

                {{-- Lista de roles --}}
                <div class="max-h-44 space-y-1 overflow-y-auto
                            rounded-lg border border-base-300 p-2">

                    @forelse ($roles as $role)
                        <div
                            wire:key="available-role-{{ $role->id }}"
                            class="rounded-md px-2 py-1
                                   hover:bg-base-200"
                        >
                            <x-checkbox
                                :label="$role->name"
                                value="{{ $role->id }}"
                                wire:model.live="roleIds"
                                class="checkbox-primary checkbox-sm"
                                omit-error
                            />
                        </div>
                    @empty
                        <p class="py-4 text-center text-sm
                                  text-base-content/60">
                            No existen roles disponibles.
                        </p>
                    @endforelse

                </div>

                @error('roleIds')
                    <p class="mt-2 text-xs text-error">
                        {{ $message }}
                    </p>
                @enderror

                @foreach ($errors->get('roleIds.*') as $message)
                    <p class="mt-1 text-xs text-error">
                        {{ $message }}
                    </p>
                @endforeach

                {{-- Roles seleccionados --}}
                <div class="mt-3 border-t border-base-300 pt-3">

                    <p class="mb-2 text-sm font-medium text-base-content">
                        Roles seleccionados
                    </p>

                    @if ($selectedRoles->isEmpty())
                        <p class="text-xs text-base-content/50">
                            Ningún rol seleccionado.
                        </p>
                    @else
                        <div class="flex flex-wrap gap-2">
                            @foreach ($selectedRoles as $role)
                                <div
                                    wire:key="selected-role-{{ $role->id }}"
                                    class="inline-flex items-center gap-1
                                           rounded-lg bg-base-200 px-2 py-1"
                                >
                                    <x-badge
                                        :value="$role->name"
                                        class="badge-primary badge-soft"
                                    />

                                    <x-button
                                        icon="o-x-mark"
                                        type="button"
                                        wire:click="confirmRemoveRole({{ $role->id }})"
                                        aria-label="Quitar rol {{ $role->name }}"
                                        class="btn-ghost btn-circle btn-xs
                                               text-error"
                                    />
                                </div>
                            @endforeach
                        </div>
                    @endif

                </div>

            </x-card>
        </div>

        {{-- Acción principal --}}
        <x-slot:actions>
            <x-button
                label="Crear usuario"
                icon="o-user-plus"
                type="submit"
                spinner="confirmSave"
                class="btn-primary"
            />
        </x-slot:actions>

    </x-form>
</div>
