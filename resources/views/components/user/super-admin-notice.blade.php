{{-- Mensaje compartido entre Show y Edit para cuentas SuperAdmin. --}}
<div class="flex items-start gap-3 rounded-xl border border-primary/20 bg-primary/5 p-4" role="status">
    <x-icon name="o-shield-check" class="mt-0.5 size-6 shrink-0 text-primary" />

    <div class="space-y-2">
        <h3 class="font-semibold text-base-content">
            Acceso administrativo global
        </h3>
        <p class="text-sm leading-6 text-base-content/80">
            Este usuario tiene el rol SuperAdmin. Las verificaciones de permisos
            realizadas mediante Laravel Gate se autorizan automáticamente,
            sin necesidad de asignar permisos individuales.
        </p>
        <p class="text-xs leading-5 text-base-content/65">
            No es necesario administrar roles adicionales en esta pantalla.
            Las restricciones de seguridad externas a Gate siguen vigentes.
        </p>
    </div>
</div>
