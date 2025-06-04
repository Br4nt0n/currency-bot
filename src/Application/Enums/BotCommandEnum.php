<?php

declare(strict_types=1);

namespace App\Application\Enums;

enum BotCommandEnum: string
{
    case START = 'start';

    case LATEST = 'latest';

    case CONVERT = 'convert';

    case CHART = 'chart';

    case CURRENCY_CHART = 'currency_chart';

    case USD_RUB = CurrencyPairEnum::USD_RUB->value;

    case USD_ARS = CurrencyPairEnum::USD_ARS->value;

    case USD_CHOICE = 'usd_choice';

    case ARS_CHOICE = 'ars_choice';

    case RUB_CHOICE = 'rub_choice';

    case BTC = 'btc';

}
