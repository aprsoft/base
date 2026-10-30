<?php

declare(strict_types=1);

namespace App\Services\Dte\V2\Mapper;

final class BoletaAfecta extends AbstractDteMapper
{
    public function type(): int
    {
        return 39;
    }
}
