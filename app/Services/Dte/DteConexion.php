<?php

namespace App\Services\Dte;

use App\Services\Dte\DteResponse as DteDteResponse;

class DteConexion
{    

    public static function hasConexion(string $apiUrl, int $timeout = 3): bool
    {
        $ch = curl_init($apiUrl);

        curl_setopt_array($ch, [
            CURLOPT_NOBODY          => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_CONNECTTIMEOUT => $timeout,
        ]);

        curl_exec($ch);        

        return !curl_errno($ch);
    }
}