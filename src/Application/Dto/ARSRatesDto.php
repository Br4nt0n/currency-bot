<?php

declare(strict_types=1);

namespace App\Application\Dto;

class ARSRatesDto extends RatesBase
{
    public function __construct(
        public float $rub,
        public float $usd,
        public float $usd_blue,
    )
    {
    }

    public function toString(): string
    {
        return "Это составит: Рубли: {$this->rub} Доллары: {$this->usd} Доллары Блю курс: {$this->usd_blue}";
    }
}
