@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Mostrar permiso" />

    <div class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold text-base-content">Detalle del permiso</h2>
                <p class="text-sm text-base-content/60">Consulta el identificador y los roles asociados.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-button label="Volver" icon="o-arrow-left" :link="route('permissions.index')"
                          no-wire-navigate class="btn-ghost btn-sm" />
                <x-button label="Editar permiso" icon="o-pencil-square"
                          :link="route('permissions.edit', ['permission' => $permission->id])"
                          no-wire-navigate class="btn-primary btn-sm" />
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <x-card shadow class="!p-4">
                <div class="mb-4 flex items-center gap-3">
                    <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-primary/10">
                        <x-icon name="o-key" class="size-6 text-primary" />
                    </div>
                    <div class="min-w-0">
                        <h3 class="break-all text-lg font-semibold text-base-content">{{ $permission->name }}</h3>
                        <p class="text-xs text-base-content/60">Permiso #{{ $permission->id }}</p>
                    </div>
                </div>
                <dl class="divide-y divide-base-300">
                    <div class="flex items-center justify-between gap-3 py-3">
                        <dt class="text-sm text-base-content/60">Guard</dt>
                        <dd><x-badge :value="$permission->guard_name" class="badge-neutral badge-soft" /></dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 py-3">
                        <dt class="text-sm text-base-content/60">Roles asociados</dt>
                        <dd><x-badge :value="$permission->roles->count()" class="badge-primary badge-soft" /></dd>
                    </div>
                </dl>
            </x-card>

            <x-card title="Roles asociados" subtitle="Perfiles que reciben este permiso" separator shadow class="!p-4">
                @if ($permission->roles->isEmpty())
                    <p class="rounded-lg border border-dashed border-base-300 p-4 text-sm text-base-content/60">
                        Este permiso no tiene roles asociados.
                    </p>
                @else
                    <div class="flex max-h-64 flex-wrap content-start gap-2 overflow-y-auto">
                        @foreach ($permission->roles as $role)
                            <x-badge :value="$role->name" icon="o-shield-check"
                                     class="badge-primary badge-soft" />
                        @endforeach
                    </div>
                @endif
            </x-card>
        </div>
    </div>
@endsection
