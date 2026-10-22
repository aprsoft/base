<?php

namespace App\Services\Central\Tenant;

use App\Models\Central\Tenant;
use DomainException;

class UpdateTenantService
{
    public function updateDomain(
        string $tenantId,
        string $domain
    ): string {
        $tenant = Tenant::query()
            ->with('domains')
            ->find($tenantId);

        if (! $tenant) {
            throw new DomainException(
                "El Tenant con ID '{$tenantId}' no existe o no está disponible."
            );
        }

        $tenantDomain = $tenant->domains->first();

        if (! $tenantDomain) {
            throw new DomainException(
                "El Tenant '{$tenantId}' no tiene un dominio asociado."
            );
        }

        $tenantDomain->update([
            'domain' => trim($domain),
        ]);

        return $tenantDomain->domain;
    }
}