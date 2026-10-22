<?php

declare(strict_types=1);

namespace App\Services\Central\Tenant;

use App\Models\Central\Tenant;
use DomainException;

class TenantLifecycleService
{
    public function suspend(string $tenantId): void
    {
        $tenant = Tenant::query()->find($tenantId);

        if (! $tenant) {
            throw new DomainException(
                "El Tenant con ID '{$tenantId}' no existe o ya está suspendido."
            );
        }

        $tenant->delete();
    }

    public function restore(string $tenantId): void
    {
        $tenant = Tenant::onlyTrashed()->find($tenantId);

        if (! $tenant) {
            throw new DomainException(
                "El Tenant con ID '{$tenantId}' no existe o no está suspendido."
            );
        }

        $tenant->restore();
    }

    public function destroy(string $tenantId): void
    {
        $tenant = Tenant::onlyTrashed()->find($tenantId);
  

        if (! $tenant) {
            throw new DomainException(
                "El Tenant con ID '{$tenantId}' no existe o no está suspendido."
            );
        }

        /*
         * IMPORTANTE:
         *
         * SSR tiene registrado Tenant::forceDeleting()
         * en TenancyServiceProvider.
         *
         * Antes de eliminar definitivamente el registro,
         * se ejecuta Jobs\DeleteDatabase::dispatchSync().
         */
        $tenant->forceDelete();
    }
}