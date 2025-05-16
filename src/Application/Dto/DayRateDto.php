<?php

declare(strict_types=1);

namespace App\Application\Dto;

use App\Application\Enums\CurrencyPairEnum;
use App\Application\Enums\TradeDirectionEnum;
use MongoDB\BSON\UTCDateTime;

class DayRateDto
{
    public function __construct(
        public CurrencyPairEnum $pair,
        public TradeDirectionEnum $direction,
        public float $value,
        public ?UTCDateTime $date = new UTCDateTime(),
    )
    {
    }

}
