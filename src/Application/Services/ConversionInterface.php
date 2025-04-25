<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Dto\RatesBase;
use App\Application\Dto\USDRatesDto;

interface ConversionInterface
{
    public function pesoConversion(float $amount): array;

    public function dollarConversion(float $amount): RatesBase|USDRatesDto;

    public function rubleConversion(float $amount): array;
}
