<?php

declare(strict_types=1);

namespace App\Application\Dto;

class USDRatesDto extends RatesBase
{
    public function __construct(
        public float $ars,
        public float $ars_blue,
        public float $rub
    )
    {
    }

    public function toString(): string
    {
        return "Песо: {$this->ars} Песо Блю курс: {$this->ars_blue} Рубли: {$this->rub}";
    }
}
