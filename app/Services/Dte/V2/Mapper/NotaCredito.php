<?php

declare(strict_types=1);

namespace App\Services\Dte\V2\Mapper;

final class NotaCredito extends AbstractDteMapper
{
    public function type(): int
    {
        return 61;
    }

    protected function validateSpecific(array $source): void
    {
        $this->requireReferences($source);
    }
}
