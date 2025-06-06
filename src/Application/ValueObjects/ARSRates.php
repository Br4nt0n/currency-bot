<?php

declare(strict_types=1);

namespace App\Application\ValueObjects;

use InvalidArgumentException;

class ARSRates extends RatesBase
{
    public function __construct(
        public float $rub,
        public float $usd,
        public float $usd_blue,
    )
    {
        if ($rub < 0 || $usd < 0 || $usd_blue < 0) {
            throw new InvalidArgumentException("Rates must be non-negative");
        }
    }

    public function toString(): string
    {
        return "Это составит: Рубли: {$this->rub} Доллары: {$this->usd} Доллары Блю курс: {$this->usd_blue}";
    }

    public function getRub(): float
    {
        return $this->rub;
    }

    public function getUsd(): float
    {
        return $this->usd;
    }

    public function getUsdBlue(): float
    {
        return $this->usd_blue;
    }

}
