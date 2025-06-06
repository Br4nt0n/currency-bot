<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\ValueObjects\RatesBase;

interface ConversionInterface
{
    public function pesoConversion(float $amount): RatesBase;

    public function dollarConversion(float $amount): RatesBase;

    public function rubleConversion(float $amount): RatesBase;
}
