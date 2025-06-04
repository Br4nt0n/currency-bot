<?php

declare(strict_types=1);

namespace App\Application\Dto;

use App\Application\Enums\CurrencyCodeEnum;

class BtcUsdDto
{
    public function __construct(
        public ?float $last,
        public ?float $buy,
        public ?float $sell,
        public ?string $currency = CurrencyCodeEnum::USD->value,
    )
    {
    }

}
