@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Ver Permiso" />

    <div class="space-y-6">

        {{-- Información del permiso --}}
        <div
            class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm
                   dark:border-gray-800 dark:bg-white/[0.03]"
        >
            <div class="mb-6">
                <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">
                    Información del permiso
                </h3>

                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Detalle del permiso y roles asociados.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        ID
                    </p>

                    <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-white/90">
                        {{ $permission->id }}
                    </p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Nombre
                    </p>

                    <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-white/90">
                        {{ $permission->name }}
                    </p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Guard
                    </p>

                    <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-white/90">
                        {{ $permission->guard_name }}
                    </p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Roles asociados
                    </p>

                    <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-white/90">
                        {{ $permission->roles->count() }}
                    </p>
                </div>

            </div>
        </div>

        {{-- Roles asociados --}}
        <div
            class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm
                   dark:border-gray-800 dark:bg-white/[0.03]"
        >
            <div class="mb-5">
                <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">
                    Roles
                </h3>

                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Roles que actualmente tienen asignado este permiso.
                </p>
            </div>

            @if ($permission->roles->isNotEmpty())

                <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">

                    <div class="overflow-x-auto">

                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">

                            <thead class="bg-gray-50 dark:bg-gray-900/50">
                                <tr>
                                    <th
                                        class="px-5 py-3 text-left text-xs font-medium uppercase
                                               tracking-wider text-gray-500 dark:text-gray-400"
                                    >
                                        ID
                                    </th>

                                    <th
                                        class="px-5 py-3 text-left text-xs font-medium uppercase
                                               tracking-wider text-gray-500 dark:text-gray-400"
                                    >
                                        Rol
                                    </th>
                                </tr>
                            </thead>

                            <tbody
                                class="divide-y divide-gray-200 bg-white
                                       dark:divide-gray-800 dark:bg-transparent"
                            >
                                @foreach ($permission->roles as $role)
                                    <tr>
                                        <td
                                            class="whitespace-nowrap px-5 py-4 text-sm
                                                   text-gray-600 dark:text-gray-400"
                                        >
                                            {{ $role->id }}
                                        </td>

                                        <td
                                            class="px-5 py-4 text-sm font-medium
                                                   text-gray-800 dark:text-white/90"
                                        >
                                            {{ $role->name }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>

                        </table>

                    </div>
                </div>

            @else

                <div
                    class="rounded-xl border border-dashed border-gray-300
                           bg-gray-50 px-5 py-8 text-center
                           dark:border-gray-700 dark:bg-gray-900/40"
                >
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Este permiso no está asociado a ningún rol.
                    </p>
                </div>

            @endif
        </div>

        {{-- Acciones --}}
        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

            <a
                href="{{ route('permissions.index') }}"
                class="inline-flex items-center justify-center rounded-lg
                       border border-gray-300 bg-white px-4 py-2.5
                       text-sm font-medium text-gray-700 shadow-sm
                       transition hover:bg-gray-50
                       dark:border-gray-700 dark:bg-gray-800
                       dark:text-gray-300 dark:hover:bg-gray-700"
            >
                Volver
            </a>

            <a
                href="{{ route('permissions.edit', $permission->id) }}"
                class="inline-flex items-center justify-center rounded-lg
                       bg-blue-600 px-4 py-2.5
                       text-sm font-medium text-white shadow-sm
                       transition hover:bg-blue-700"
            >
                Editar permiso
            </a>

        </div>

    </div>
@endsection