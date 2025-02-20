<?php

namespace App\Application\Enums;

enum CurrencyEnum: string
{
    case Ruble = 'ruble';

    case Dollar = 'dollar';

    case Peso = 'peso';

    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

}
