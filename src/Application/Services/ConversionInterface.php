<?php

declare(strict_types=1);

namespace App\Application\Services;

interface ConversionInterface
{
    public const string DOLLAR_BLUE = 'dollar_blue';

    public const string RUB_ARS = 'rub_ars';

    public const string RUB_USD = 'rub_usd';

    public const string USD_RUB = 'usd_rub';

    public const string USD_ARS = 'usd_ars';

    public function pesoConversion(float $amount): array;

    public function dollarConversion(float $amount): array;

    public function rubleConversion(float $amount): array;
}
