@php
    /**
     * Solo se agrupan los permisos que el componente Livewire ya autorizó.
     * La agrupación es visual; no modifica roles, guards ni permisos en BD.
     */
    $permissionGroups = $permissions
        ->groupBy(static function ($permission): string {
            $dot = strpos($permission->name, '.');

            return $dot === false || $dot === 0
                ? 'otros'
                : substr($permission->name, 0, $dot);
        })
        ->sortKeys();

    $groupsForAlpine = $permissionGroups
        ->map(static fn ($items, $module) => [
            'module' => (string) $module,
            'ids' => $items->pluck('id')->map(static fn ($id) => (int) $id)->values()->all(),
            'names' => $items->pluck('name')->values()->all(),
        ])
        ->values()
        ->all();

    $moduleLabels = [
        'users' => 'Usuarios',
        'roles' => 'Roles',
        'permissions' => 'Permisos',
        'profile' => 'Perfil',
        'errors' => 'Registros de errores',
        'otros' => 'Otros',
    ];
@endphp

<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-base-content">
                {{ $editing ? 'Editar rol' : 'Nuevo rol' }}
            </h2>
            <p class="text-sm text-base-content/60">
                {{ $editing ? 'Actualiza el nombre y los permisos asignados.' : 'Crea un perfil y selecciona sus permisos.' }}
            </p>
        </div>

        <x-button label="Volver" icon="o-arrow-left" :link="route('roles.index')"
                  no-wire-navigate class="btn-ghost btn-sm" />
    </div>

    <x-form wire:submit="{{ $editing ? 'update' : 'save' }}" class="!gap-4">
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
                        <x-icon name="o-information-circle"
                                class="mt-0.5 size-5 shrink-0 text-blue-light-700 dark:text-blue-light-300" />
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

                <div
                    x-data="{
                        search: '',
                        groups: @js($groupsForAlpine),

                        selected() {
                            return ($wire.permissionIds || []).map(id => Number(id));
                        },

                        group(module) {
                            return this.groups.find(item => item.module === module);
                        },

                        selectedInGroup(module) {
                            const group = this.group(module);
                            if (!group) return 0;
                            const selected = new Set(this.selected());
                            return group.ids.filter(id => selected.has(id)).length;
                        },

                        allInGroup(module) {
                            const group = this.group(module);
                            return !!group && group.ids.length > 0
                                && this.selectedInGroup(module) === group.ids.length;
                        },

                        toggleGroup(module) {
                            const group = this.group(module);
                            if (!group) return;

                            const selected = new Set(this.selected());
                            const remove = this.allInGroup(module);

                            for (const id of group.ids) {
                                if (remove) selected.delete(id);
                                else selected.add(id);
                            }

                            $wire.$set('permissionIds', Array.from(selected));
                        },

                        selectAll() {
                            $wire.$set('permissionIds',
                                this.groups.flatMap(group => group.ids));
                        },

                        clearAll() {
                            $wire.$set('permissionIds', []);
                        },

                        normalizedSearch() {
                            return this.search.trim().toLocaleLowerCase();
                        },

                        matchesPermission(module, name) {
                            const term = this.normalizedSearch();
                            return term === ''
                                || module.toLocaleLowerCase().includes(term)
                                || name.toLocaleLowerCase().includes(term);
                        },

                        matchesGroup(module) {
                            const group = this.group(module);
                            return !!group && group.names.some(name =>
                                this.matchesPermission(module, name));
                        },

                        hasMatches() {
                            return this.groups.some(group => this.matchesGroup(group.module));
                        }
                    }"
                    class="space-y-3"
                >
                    <label class="block">
                        <span class="mb-1 block text-xs font-medium text-base-content/70">Buscar permisos</span>
                        <input type="search" x-model.debounce.150ms="search"
                               placeholder="Ej.: users, roles o edit"
                               autocomplete="off"
                               class="input input-bordered w-full" />
                    </label>

                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="text-xs text-base-content/70">
                            <span x-text="selected().length"></span> de {{ $permissions->count() }} seleccionados
                        </span>
                        <div class="flex gap-2">
                            <button type="button" x-on:click="selectAll()"
                                    class="btn btn-outline btn-xs" wire:loading.attr="disabled">
                                Todos
                            </button>
                            <button type="button" x-on:click="clearAll()"
                                    class="btn btn-ghost btn-xs" wire:loading.attr="disabled">
                                Ninguno
                            </button>
                        </div>
                    </div>

                    <div class="max-h-80 space-y-3 overflow-y-auto rounded-lg border border-base-300 p-3">
                        @forelse ($permissionGroups as $module => $modulePermissions)
                            <section wire:key="permission-module-{{ md5($module) }}"
                                     x-show="matchesGroup(@js($module))"
                                     class="rounded-lg border border-base-300 p-3">
                                <div class="mb-2 flex items-center justify-between gap-2">
                                    <button type="button"
                                            x-on:click="toggleGroup(@js($module))"
                                            :aria-pressed="allInGroup(@js($module))"
                                            wire:loading.attr="disabled"
                                            class="flex items-center gap-2 rounded-md text-left text-sm font-semibold text-base-content hover:text-primary">
                                        <span aria-hidden="true"
                                              x-text="allInGroup(@js($module)) ? '☑' : (selectedInGroup(@js($module)) ? '◩' : '☐')"></span>
                                        <span>{{ $moduleLabels[$module] ?? \Illuminate\Support\Str::headline($module) }}</span>
                                    </button>
                                    <span class="text-xs tabular-nums text-base-content/60">
                                        <span x-text="selectedInGroup(@js($module))"></span>/{{ $modulePermissions->count() }}
                                    </span>
                                </div>

                                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                    @foreach ($modulePermissions as $permission)
                                        <div wire:key="role-permission-{{ $permission->id }}"
                                             x-show="matchesPermission(@js($module), @js($permission->name))"
                                             class="rounded-md px-1 py-1 hover:bg-base-200">
                                            <x-checkbox :label="$permission->name"
                                                        value="{{ $permission->id }}"
                                                        wire:model.number.live="permissionIds"
                                                        class="checkbox-primary checkbox-sm"
                                                        omit-error />
                                        </div>
                                    @endforeach
                                </div>
                            </section>
                        @empty
                            <p class="py-4 text-center text-sm text-base-content/60">
                                No existen permisos disponibles.
                            </p>
                        @endforelse

                        @if ($permissionGroups->isNotEmpty())
                            <p x-show="!hasMatches()" class="py-4 text-center text-sm text-base-content/60">
                                No hay permisos que coincidan con la búsqueda.
                            </p>
                        @endif
                    </div>
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
            @if ($editing)
                <x-button label="Guardar cambios" icon="o-check" type="submit"
                          spinner="update" class="btn-primary" />
            @else
                <x-button label="Crear rol" icon="o-plus" type="submit"
                          spinner="save" class="btn-primary" />
            @endif
        </x-slot:actions>
    </x-form>
</div>