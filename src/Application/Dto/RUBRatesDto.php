<?php

declare(strict_types=1);

namespace App\Application\Dto;

class RUBRatesDto extends RatesBase
{
    public function __construct(
        public float $ars,
        public float $usd
    )
    {
    }

    public function toString(): string
    {
        return "Это составит: Песо: {$this->ars} Доллары: {$this->usd}";
    }
}
