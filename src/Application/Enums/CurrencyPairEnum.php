<?php

declare(strict_types=1);

namespace App\Application\Enums;

enum CurrencyPairEnum: string
{
    case USD_RUB = 'USD_RUB';

    case USD_ARS = 'USD_ARS';

    case RUB_ARS = 'RUB_ARS';
}
