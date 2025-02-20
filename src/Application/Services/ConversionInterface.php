<?php

declare(strict_types=1);

namespace App\Application\Services;

interface ConversionInterface
{
    public const DOLLAR_BLUE = 'dollar_blue';

    public const RUB_ARS = 'rub_ars';

    public const RUB_USD = 'rub_usd';

    public function pesoConversion(float $amount): array;

    public function dollarConversion(float $amount): array;

    public function rubleConversion(float $amount): array;
}
