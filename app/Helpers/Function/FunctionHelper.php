<?php

declare(strict_types=1);

namespace App\Helpers\Function;

use Illuminate\Support\Facades\Auth;

final class FunctionHelper
{
    public static function routePrefix(): string
    {
        $host = request()->getHost();
        $centralDomains = config('tenancy.central_domains', []);

        if (in_array($host, $centralDomains, true)) {
            return 'central';
        }

        return Auth::getDefaultDriver() === 'customer' ? 'customer' : 'tenant';
    }
}