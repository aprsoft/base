<?php

declare(strict_types=1);

namespace App\Services\Dte\V2\Mapper;

final class NotaDebito extends AbstractDteMapper
{
    public function type(): int
    {
        return 56;
    }

    protected function validateSpecific(array $source): void
    {
        $this->requireReferences($source);
    }
}
