<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                Administración de usuarios
            </p>
            <h2 class="mt-1 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">
                Perfil del usuario
            </h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Consulta la información, los roles y el estado de esta cuenta.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <x-button label="Volver a usuarios" icon="o-arrow-left"
                      :link="route('users.index')"  no-wire-navigate class="btn-ghost" />

            @if (auth('web')->user()?->can('users.edit') && ! $user->trashed())
                <x-button label="Editar usuario" icon="o-pencil-square"
                          :link="route('users.edit', ['user' => $user->id])"
                          no-wire-navigate
                          class="btn-primary" />
            @endif
        </div>
    </div>

    <livewire:pdf.pdf-actions type="user.profile" :record-id="$user->id" :key="'user-pdf-'.$user->id" />

    <x-card shadow>
        <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
            <div class="flex size-16 shrink-0 items-center justify-center rounded-2xl bg-brand-50 text-xl font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-300"
                 aria-hidden="true">
                {{ $user->initials() }}
            </div>

            <div class="min-w-0 flex-1">
                <h3 class="break-words text-xl font-semibold text-gray-900 dark:text-white">
                    {{ $user->name }}
                </h3>
                <p class="mt-1 break-all text-sm text-gray-500 dark:text-gray-400">
                    {{ $user->email }}
                </p>
                <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">
                    Identificador #{{ $user->id }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @if ($user->trashed())
                    <x-badge value="Suspendido" icon="o-no-symbol" class="badge-error badge-soft" />
                @else
                    <x-badge value="Activo" icon="o-check-circle" class="badge-success badge-soft" />
                @endif
            </div>
        </div>
    </x-card>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-card title="Datos de la cuenta"
                subtitle="Información registrada en el sistema"
                separator shadow>
            <dl class="divide-y divide-gray-100 dark:divide-gray-800">
                <div class="flex items-start gap-3 py-4 first:pt-0">
                    <x-icon name="o-user" class="mt-0.5 size-5 shrink-0 text-gray-400" />
                    <div class="min-w-0">
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Nombre completo</dt>
                        <dd class="mt-1 break-words text-sm font-medium text-gray-900 dark:text-white">{{ $user->name }}</dd>
                    </div>
                </div>

                <div class="flex items-start gap-3 py-4">
                    <x-icon name="o-envelope" class="mt-0.5 size-5 shrink-0 text-gray-400" />
                    <div class="min-w-0">
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Correo electrónico</dt>
                        <dd class="mt-1 break-all text-sm font-medium text-gray-900 dark:text-white">{{ $user->email }}</dd>
                    </div>
                </div>

                <div class="flex items-start gap-3 pt-4">
                    <x-icon name="o-shield-check" class="mt-0.5 size-5 shrink-0 text-gray-400" />
                    <div class="min-w-0">
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Verificación del correo</dt>
                        <dd class="mt-2">
                            @if ($user->email_verified_at)
                                <x-badge value="Verificado" class="badge-success badge-soft" />
                            @else
                                <x-badge value="No verificado" class="badge-warning badge-soft" />
                            @endif
                        </dd>
                    </div>
                </div>
            </dl>
        </x-card>

        @if ($isSuperAdminTarget)
            <x-card shadow>
                <x-user.super-admin-notice />
            </x-card>
        @else
        <x-card title="Roles asignados"
                subtitle="Roles de autorización asociados a la cuenta"
                separator shadow>
            @if ($roles->isEmpty())
                <div class="rounded-xl border border-dashed border-gray-200 px-4 py-8 text-center dark:border-gray-700">
                    <x-icon name="o-shield-exclamation" class="mx-auto size-8 text-gray-400" />
                    <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                        Este usuario no tiene roles asignados.
                    </p>
                </div>
            @else
                <div class="flex flex-wrap gap-2">
                    @foreach ($roles as $role)
                        <x-badge :value="$role->name" icon="o-shield-check"
                                 class="badge-primary badge-soft" />
                    @endforeach
                </div>
                <p class="mt-5 text-xs leading-5 text-gray-500 dark:text-gray-400">
                    Los permisos efectivos dependen de los roles y las reglas de autorización de la aplicación.
                </p>
            @endif
        </x-card>
        @endif
    </div>

    <x-card title="Información de registro"
            subtitle="Fechas relevantes de la cuenta"
            separator shadow>
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <div class="flex items-start gap-3">
                <x-icon name="o-calendar-days" class="mt-0.5 size-5 shrink-0 text-gray-400" />
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Fecha de creación</p>
                    <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">
                        {{ $user->created_at?->format('d/m/Y H:i') ?? 'Sin información' }}
                    </p>
                </div>
            </div>

            <div class="flex items-start gap-3">
                <x-icon name="o-clock" class="mt-0.5 size-5 shrink-0 text-gray-400" />
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Última actualización</p>
                    <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">
                        {{ $user->updated_at?->format('d/m/Y H:i') ?? 'Sin información' }}
                    </p>
                </div>
            </div>

            @if ($user->trashed())
                <div class="flex items-start gap-3">
                    <x-icon name="o-no-symbol" class="mt-0.5 size-5 shrink-0 text-red-500" />
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Fecha de suspensión</p>
                        <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">
                            {{ $user->deleted_at?->format('d/m/Y H:i') ?? 'Sin información' }}
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </x-card>
</div>
