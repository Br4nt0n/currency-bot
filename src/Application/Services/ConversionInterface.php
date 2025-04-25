<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Dto\ARSRatesDto;
use App\Application\Dto\RatesBase;
use App\Application\Dto\RUBRatesDto;
use App\Application\Dto\USDRatesDto;

interface ConversionInterface
{
    public function pesoConversion(float $amount): RatesBase|ARSRatesDto;

    public function dollarConversion(float $amount): RatesBase|USDRatesDto;

    public function rubleConversion(float $amount): RatesBase|RUBRatesDto;
}
