<?php

declare(strict_types=1);

namespace App\Infrastructure\Services;

use App\Application\Dto\ARSRatesDto;
use App\Application\Dto\RatesBase;
use App\Application\Dto\RUBRatesDto;
use App\Application\Dto\USDRatesDto;
use App\Application\Services\ConversionInterface;
use App\Application\Services\CurrencyServiceInterface;
use Redis;

final readonly class ConversionService implements ConversionInterface
{
    public function __construct(private Redis $redis, private CurrencyServiceInterface $service)
    {
    }

    public function pesoConversion(float $amount): RatesBase|ARSRatesDto
    {
        $usdBlue = $this->redis->get(CurrencyServiceInterface::DOLLAR_BLUE_SELL);
        $arsRub = $this->redis->get(CurrencyServiceInterface::RUB_ARS);
        $arsUsd = $this->redis->get(CurrencyServiceInterface::USD_ARS);

        if ($usdBlue === false) {
            $usdBlue = $this->service->getDollarBlueRate()->sell;
        }

        if ($arsRub === false) {
            $arsRub = $this->service->getRubRates()->rubArs;
        }

        if ($arsUsd === false) {
            $arsUsd = $this->service->getUsdRates()->usdArs;
        }

        return new ARSRatesDto(
            rub: round($amount / $arsRub, 2),
            usd: round($amount / (float)$arsUsd, 2),
            usd_blue: round($amount / (float)$usdBlue, 2),
        );
    }

    public function dollarConversion(float $amount): RatesBase|USDRatesDto
    {
        $pesoBlueValue = $this->redis->get(CurrencyServiceInterface::DOLLAR_BLUE_BUY);
        $usdRub = $this->redis->get(CurrencyServiceInterface::USD_RUB);
        $usdArs = $this->redis->get(CurrencyServiceInterface::USD_ARS);

        if ($pesoBlueValue === false) {
            $pesoBlueValue = $this->service->getDollarBlueRate()->buy;
        }

        if ($usdRub === false || $usdArs === false) {
            $usdRate = $this->service->getUsdRates();
            $usdRub = $usdRate->usdRub;
            $usdArs = $usdRate->usdArs;
        }

        return new USDRatesDto(
            ars: round($amount * $usdArs, 2),
            ars_blue: round($amount * $pesoBlueValue, 2),
            rub: round($amount * $usdRub),
        );
    }

    public function rubleConversion(float $amount): RatesBase|RUBRatesDto
    {
        $rubArs = $this->redis->get(CurrencyServiceInterface::RUB_ARS);
        $rubUsd = $this->redis->get(CurrencyServiceInterface::RUB_USD);

        if ($rubArs === false || $rubUsd === false) {
            $rubRates = $this->service->getRubRates();
            $rubArs = $rubRates->rubArs;
            $rubUsd = $rubRates->rubUsd;
        }

        return new RUBRatesDto(
            ars: round($amount * $rubArs, 2),
            usd: round($amount * $rubUsd, 2),
        );
    }
}
