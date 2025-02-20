<?php

declare(strict_types=1);

namespace App\Application\Services;

use Redis;

final class ConversionService implements ConversionInterface
{
    public function __construct(private readonly Redis $redis, private readonly CurrencyServiceInterface $service)
    {
    }

    public function pesoConversion(float $amount): array
    {
        $dollarValue = $this->redis->get(self::DOLLAR_BLUE);
        $rubleValue = $this->redis->get(self::RUB_ARS);

        if ($dollarValue === null) {
            $dollarValue = $this->service->getDollarBlueRate();
        }

        if ($rubleValue === null) {
            $rubleValue = $this->service->getRubArsRate();
        }

        return [
            'amount' => $amount,
            'dollarBlue' => round($amount / (float)$dollarValue, 2),
            'rubble' => round($amount / $rubleValue),
        ];
    }

    public function dollarConversion(float $amount): array
    {
        $pesoValue = $this->redis->get(self::DOLLAR_BLUE);
        $rubleValue = $this->redis->get(self::RUB_ARS);

        if ($pesoValue === null) {
            $pesoValue = $this->service->getDollarBlueRate();
        }

        if ($rubleValue === null) {
            $rubleValue = $this->service->getRubArsRate();
        }

        return [
            'amount' => $amount,
            'peso' => round($amount * (float)$pesoValue, 2),
            'rubble' => round($amount * (float)$rubleValue),
        ];
    }

    public function rubleConversion(float $amount): array
    {
        $pesoValue = $this->redis->get(self::RUB_ARS);
        $dollarValue = $this->redis->get(self::RUB_USD);

        if ($pesoValue === null) {
            $pesoValue = $this->service->getRubArsRate();
        }

        if ($dollarValue === null) {
            $dollarValue = $this->service->getRubUsdRate();
        }

        return [
            'amount' => $amount,
            'dollar' => round($amount * (float)$dollarValue, 2),
            'peso' => round($amount * (float)$pesoValue, 2),
        ];
    }

}
