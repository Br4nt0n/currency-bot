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
        $usdBlue = $this->redis->get(CurrencyServiceInterface::DOLLAR_BLUE_SELL);
        $arsRub = $this->redis->get(CurrencyServiceInterface::RUB_ARS);
        $arsUsd = $this->redis->get(CurrencyServiceInterface::USD_ARS);

        if ($usdBlue === false) {
            $usdBlue = $this->service->getDollarBlueRate()?->sell;
        }

        if ($arsRub === false) {
            $arsRub = $this->service->getRubRates()->rubArs;
        }

        if ($arsUsd === false) {
            $arsUsd = $this->service->getUsdRates()->usdArs;
        }

        return [
            'amount' => $amount,
            'usd_blue' => round($amount / (float)$usdBlue, 2),
            'usd_official' => round($amount / (float)$arsUsd, 2),
            'ruble' => round($amount / $arsRub),
        ];
    }

    public function dollarConversion(float $amount): array
    {
        $pesoBlueValue = $this->redis->get(CurrencyServiceInterface::DOLLAR_BLUE_BUY);
        $usdRub = $this->redis->get(CurrencyServiceInterface::USD_RUB);
        $usdArs = $this->redis->get(CurrencyServiceInterface::USD_ARS);

        if ($pesoBlueValue === null) {
            $pesoBlueValue = $this->service->getDollarBlueRate()?->buy;
        }

        if ($usdRub === null || $usdArs === null) {
            $usdRate = $this->service->getUsdRates();
            $usdRub = $usdRate->usdRub;
            $usdArs = $usdRate->usdArs;
        }

        return [
            'amount' => $amount,
            'peso' => round($amount * $usdArs, 2),
            'peso_blue' => round($amount * $pesoBlueValue, 2),
            'rubble' => round($amount * $usdRub),
        ];
    }

    public function rubleConversion(float $amount): array
    {
        $rubArs = $this->redis->get(CurrencyServiceInterface::RUB_ARS);
        $rubUsd = $this->redis->get(CurrencyServiceInterface::RUB_USD);

        if ($rubArs === null || $rubUsd === null) {
            $rubRates = $this->service->getRubRates();
            $rubArs = $rubRates->rubArs;
            $rubUsd = $rubRates->rubUsd;
        }

        return [
            'amount' => $amount,
            'usd' => round($amount * $rubUsd, 2),
            'peso' => round($amount * $rubArs, 2),
        ];
    }

}
