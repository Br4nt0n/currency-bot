<?php

namespace App\Application\Enums;

enum CurrencyCodeEnum: string
{
    case RUB = 'RUB';

    case USD = 'USD';

    case ARS = 'ARS';

    case EUR = 'EUR';

    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
