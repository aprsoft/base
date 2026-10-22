
@php
    $alertTypes = ['success', 'error', 'warning', 'info'];

    // Canal de notificaciones existente.
    $realtimeChannel = auth('web')->check()
        ? 'users.' . auth('web')->id()
        : null;
@endphp

<div
    class="space-y-3"
    x-data="{
        type: null,
        message: '',
        timer: null,

        show(type, message) {
            if (
                ! ['success', 'error', 'warning', 'info'].includes(type)
                || typeof message !== 'string'
                || ! message.trim()
            ) {
                return;
            }

            clearTimeout(this.timer);

            this.type = type;
            this.message = message;

            const duration = type === 'error' ? 8000 : 7000;

            this.timer = setTimeout(() => {
                this.type = null;
                this.message = '';
            }, duration);
        }
    }"
    x-on:app-alert.window="
        show($event.detail.type, $event.detail.message)
    "
    x-on:app-job-notification.window="
        show($event.detail.type, $event.detail.message)
    "
>
    {{-- Alertas de sesión: redirecciones Laravel --}}

    @foreach ($alertTypes as $alertType)
        @if (session()->has($alertType))
            <div
                x-data="{ visible: true }"
                x-init="
                    setTimeout(
                        () => visible = false,
                        {{ $alertType === 'error' ? 8000 : 5000 }}
                    )
                "
                x-show="visible"
                x-transition
            >
                <x-ui.alert :variant="$alertType">
                    {{ session($alertType) }}
                </x-ui.alert>
            </div>
        @endif
    @endforeach

    {{-- Errores de validación de solicitudes HTTP --}}

    @if ($errors->any())
        <x-ui.alert variant="error">
            <ul class="list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-ui.alert>
    @endif

    {{-- Alertas inmediatas Livewire y Echo --}}

    @foreach ($alertTypes as $alertType)
        <div
            x-show="message && type === '{{ $alertType }}'"
            x-transition
            x-cloak
            style="display: none;"
        >
            <x-ui.alert :variant="$alertType">
                <span x-text="message"></span>
            </x-ui.alert>
        </div>
    @endforeach
</div>

{{-- Notificaciones existentes de Laravel Echo --}}

@if ($realtimeChannel)
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (!window.Echo) {
                return;
            }

            window.Echo
                .private(@js($realtimeChannel))
                .listen('.job-notification', (event) => {
                    window.dispatchEvent(
                        new CustomEvent('app-job-notification', {
                            detail: {
                                type: event.type,
                                message: event.message
                            }
                        })
                    );
                });
        });
    </script>
@endif
