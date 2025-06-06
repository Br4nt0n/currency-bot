<?php

declare(strict_types=1);

namespace App\Application\ValueObjects;

use InvalidArgumentException;

class RUBRates extends RatesBase
{
    public function __construct(
        private readonly float $ars,
        private readonly float $usd
    )
    {
        if ($ars < 0 || $usd < 0) {
            throw new InvalidArgumentException("Rates must be non-negative");
        }
    }

    public function toString(): string
    {
        return "Это составит: Песо: {$this->ars} Доллары: {$this->usd}";
    }

    public function getArs(): float
    {
        return $this->ars;
    }

    public function getUsd(): float
    {
        return $this->usd;
    }

}
