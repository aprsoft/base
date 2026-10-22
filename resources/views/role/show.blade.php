@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Ver Rol" />

    <div class="space-y-6">

        {{-- Información del rol --}}
        <div
            class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm
                   dark:border-gray-800 dark:bg-white/[0.03]"
        >
            <div class="mb-6">
                <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">
                    Información del rol
                </h3>

                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Detalle del rol y permisos asociados.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        ID
                    </p>

                    <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-white/90">
                        {{ $role->id }}
                    </p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Nombre
                    </p>

                    <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-white/90">
                        {{ $role->name }}
                    </p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Guard
                    </p>

                    <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-white/90">
                        {{ $role->guard_name }}
                    </p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Permisos asociados
                    </p>

                    <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-white/90">
                        {{ $role->permissions->count() }}
                    </p>
                </div>

            </div>
        </div>


        {{-- Permisos --}}
        <div
            class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm
                   dark:border-gray-800 dark:bg-white/[0.03]"
        >
            <div class="mb-5">
                <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">
                    Permisos
                </h3>

                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Permisos asignados directamente a este rol.
                </p>
            </div>

            @if ($role->permissions->isNotEmpty())

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
                                        Permiso
                                    </th>
                                </tr>
                            </thead>

                            <tbody
                                class="divide-y divide-gray-200 bg-white
                                       dark:divide-gray-800 dark:bg-transparent"
                            >
                                @foreach ($role->permissions as $permission)
                                    <tr>
                                        <td
                                            class="whitespace-nowrap px-5 py-4 text-sm
                                                   text-gray-600 dark:text-gray-400"
                                        >
                                            {{ $permission->id }}
                                        </td>

                                        <td
                                            class="px-5 py-4 text-sm font-medium
                                                   text-gray-800 dark:text-white/90"
                                        >
                                            {{ $permission->name }}
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
                        Este rol no tiene permisos asociados directamente.
                    </p>
                </div>

            @endif
        </div>


        {{-- Acciones --}}
        <div class="flex justify-end">
            <a
                href="{{ route('roles.index') }}"
                class="inline-flex items-center justify-center rounded-lg
                       border border-gray-300 bg-white px-4 py-2.5
                       text-sm font-medium text-gray-700 shadow-sm
                       transition hover:bg-gray-50
                       dark:border-gray-700 dark:bg-gray-800
                       dark:text-gray-300 dark:hover:bg-gray-700"
            >
                Volver
            </a>
        </div>

    </div>
@endsection