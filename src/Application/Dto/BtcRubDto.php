<?php

declare(strict_types=1);

namespace App\Application\Dto;

use App\Application\Enums\CurrencyCodeEnum;

class BtcRubDto
{
    public function __construct(
        public ?float $last,
        public ?float $buy,
        public ?float $sell,
        public ?string $currency = CurrencyCodeEnum::RUB->value
    )
    {
    }

}
