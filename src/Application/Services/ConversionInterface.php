<?php

declare(strict_types=1);

namespace App\Application\Services;

interface ConversionInterface
{
    public function pesoConversion(float $amount): array;

    public function dollarConversion(float $amount): array;

    public function rubleConversion(float $amount): array;
}
