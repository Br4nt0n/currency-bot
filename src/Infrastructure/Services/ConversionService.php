<?php

declare(strict_types=1);

namespace App\Infrastructure\Services;

use App\Application\Exceptions\ConversionException;
use App\Application\ValueObjects\ARSRates;
use App\Application\ValueObjects\EURRates;
use App\Application\ValueObjects\RatesBase;
use App\Application\ValueObjects\RUBRates;
use App\Application\ValueObjects\USDRates;
use App\Application\Services\ConversionInterface;
use App\Application\Services\CurrencyServiceInterface;
use Redis;

final readonly class ConversionService implements ConversionInterface
{
    public function __construct(private Redis $redis, private CurrencyServiceInterface $service)
    {
    }

    public function pesoConversion(float $amount): RatesBase|ARSRates
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

        return new ARSRates(
            rub: round($amount / $arsRub, 2),
            usd: round($amount / (float)$arsUsd, 2),
            usd_blue: round($amount / (float)$usdBlue, 2),
        );
    }

    public function dollarConversion(float $amount): RatesBase|USDRates
    {
        $pesoBlueValue = $this->redis->get(CurrencyServiceInterface::DOLLAR_BLUE_BUY);
        $usdRub = $this->redis->get(CurrencyServiceInterface::USD_RUB);
        $usdArs = $this->redis->get(CurrencyServiceInterface::USD_ARS);

        if ($pesoBlueValue === false) {
            $pesoBlueValue = $this->service->getDollarBlueRate()->buy;
        }

        if ($usdRub === false || $usdArs === false) {
            throw new ConversionException("USD rates not found!");
        }

        return new USDRates(
            ars: round($amount * $usdArs, 2),
            ars_blue: round($amount * $pesoBlueValue, 2),
            rub: round($amount * $usdRub),
        );
    }

    public function rubleConversion(float $amount): RatesBase|RUBRates
    {
        $rubArs = $this->redis->get(CurrencyServiceInterface::RUB_ARS);
        $rubUsd = $this->redis->get(CurrencyServiceInterface::RUB_USD);

        if ($rubArs === false || $rubUsd === false) {
            throw new ConversionException("RUB rates not found!");
        }

        return new RUBRates(
            ars: round($amount * $rubArs, 2),
            usd: round($amount * $rubUsd, 2),
        );
    }

    public function euroConversion(float $amount): RatesBase
    {
        $eurRub = $this->redis->get(CurrencyServiceInterface::EUR_RUB);
        $eurArs = $this->redis->get(CurrencyServiceInterface::EUR_ARS);

        if ($eurArs === false || $eurRub === false) {
            throw new ConversionException("EURO rates not found!");
        }

        return new EURRates(
            ars: round($amount * $eurArs, 2),
            rub: round($amount * $eurRub, 2)
        );
    }

}
