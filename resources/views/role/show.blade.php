@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Mostrar rol" />

    <div class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold text-base-content">Detalle del rol</h2>
                <p class="text-sm text-base-content/60">Consulta el perfil y sus permisos asociados.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-button label="Volver" icon="o-arrow-left" :link="route('roles.index')"
                          no-wire-navigate class="btn-ghost btn-sm" />
                <x-button label="Editar rol" icon="o-pencil-square"
                          :link="route('roles.edit', ['role' => $role->id])"
                          no-wire-navigate class="btn-primary btn-sm" />
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <x-card shadow class="!p-4">
                <div class="mb-4 flex items-center gap-3">
                    <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-primary/10">
                        <x-icon name="o-shield-check" class="size-6 text-primary" />
                    </div>
                    <div class="min-w-0">
                        <h3 class="break-words text-lg font-semibold text-base-content">{{ $role->name }}</h3>
                        <p class="text-xs text-base-content/60">Rol #{{ $role->id }}</p>
                    </div>
                </div>
                <dl class="divide-y divide-base-300">
                    <div class="flex items-center justify-between gap-3 py-3">
                        <dt class="text-sm text-base-content/60">Guard</dt>
                        <dd><x-badge :value="$role->guard_name" class="badge-neutral badge-soft" /></dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 py-3">
                        <dt class="text-sm text-base-content/60">Permisos asociados</dt>
                        <dd><x-badge :value="$role->permissions->count()" class="badge-primary badge-soft" /></dd>
                    </div>
                </dl>
            </x-card>

            <x-card title="Permisos asignados" subtitle="Autorizaciones vinculadas al rol" separator shadow class="!p-4">
                @forelse ($role->permissions as $permission)
                    @if ($loop->first)
                        <div class="flex max-h-64 flex-wrap content-start gap-2 overflow-y-auto">
                    @endif
                    <x-badge :value="$permission->name" icon="o-key"
                             class="badge-primary badge-soft" />
                    @if ($loop->last)
                        </div>
                    @endif
                @empty
                    <p class="rounded-lg border border-dashed border-base-300 p-4 text-sm text-base-content/60">
                        Este rol no tiene permisos asignados.
                    </p>
                @endforelse
            </x-card>
        </div>
    </div>
@endsection
