<?php

namespace App\Services\Central\Tenant;

use App\Models\Central\Tenant;
use DomainException;
use RuntimeException;

class CreateTenantService
{
    public function create(string $tenantId): Tenant
    {
        $tenantId = trim($tenantId);

        /*
         * Tenant usa SoftDeletes.
         *
         * También comprobamos registros suspendidos/eliminados
         * lógicamente para impedir reutilizar un ID existente.
         */
        if (
            Tenant::withTrashed()
                ->where('id', $tenantId)
                ->exists()
        ) {
            throw new DomainException(
                "El Tenant con ID '{$tenantId}' ya existe."
            );
        }

        $centralDomains = config('tenancy.central_domains');

        if (
            ! is_array($centralDomains)
            || empty($centralDomains)
            || ! isset($centralDomains[0])
            || ! is_string($centralDomains[0])
            || $centralDomains[0] === ''
        ) {
            throw new RuntimeException(
                'No hay dominios centrales configurados.'
            );
        }

        /*
         * IMPORTANTE:
         * Tenant::create() dispara TenantCreated.
         *
         * En SSR este evento ejecuta sincrónicamente:
         * CreateDatabase
         * MigrateDatabase
         * SeedDatabase
         */
        $tenant = Tenant::create([
            'id' => $tenantId,
        ]);

        $tenant->domains()->create([
            'domain' => $tenantId . '.' . $centralDomains[0],
        ]);

        return $tenant;
    }
}