<?php

declare(strict_types=1);

namespace App\Application\ValueObjects;

use InvalidArgumentException;

class EURRates extends RatesBase
{
    public function __construct(
        private readonly float $ars,
        private readonly float $rub
    )
    {
        if ($ars < 0 || $rub < 0) {
            throw new InvalidArgumentException("Rates must be non-negative");
        }
    }

    public function toString(): string
    {
        return "Это составит: Песо: {$this->ars} Рубли: {$this->rub}";
    }

    public function getArs(): float
    {
        return $this->ars;
    }

    public function getRub(): float
    {
        return $this->rub;
    }

}
