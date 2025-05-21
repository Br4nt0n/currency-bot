<?php

declare(strict_types=1);

namespace App\Application\Enums;

enum CurrencyPairEnum: string
{
    case USD_RUB = 'USD_RUB';

    case USD_ARS = 'USD_ARS';

    case RUB_ARS = 'RUB_ARS';

    public function description(): string
    {
        return match ($this) {
            self::USD_RUB => 'доллар к рублю',
            self::USD_ARS => 'доллар к песо',
            self::RUB_ARS => 'рубль к песо',
         };
    }

    public function color(): string
    {
        return match ($this) {
            self::USD_RUB => 'red',
            self::USD_ARS => 'green',
            self::RUB_ARS => 'orange',
        };
    }
}
