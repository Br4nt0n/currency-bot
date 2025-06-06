<?php

declare(strict_types=1);

namespace App\Application\ValueObjects;

use InvalidArgumentException;

class USDRates extends RatesBase
{
    public function __construct(
        private readonly float $ars,
        private readonly float $ars_blue,
        private readonly float $rub
    )
    {
        if ($ars < 0 || $ars_blue < 0 || $rub < 0) {
            throw new InvalidArgumentException("Rates must be non-negative");
        }
    }

    public function toString(): string
    {
        return "Это составит: Песо: {$this->ars} Песо Блю курс: {$this->ars_blue} Рубли: {$this->rub}";
    }

    public function getArs(): float
    {
        return $this->ars;
    }

    public function getArsBlue(): float
    {
        return $this->ars_blue;
    }

    public function getRub(): float
    {
        return $this->rub;
    }

}
