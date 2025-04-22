<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Dto\RubDto;
use App\Application\Dto\UsdDto;
use App\Application\Exceptions\CurrencyException;
use App\Application\Repositories\BlueLyticsRepository;
use App\Application\Repositories\ExchangeRateRepository;
use Redis;

final class CurrencyService implements CurrencyServiceInterface
{
    private const int TTL = 3600 * 24;

    public function __construct(
        private readonly ExchangeRateRepository $exchangeRateRepository,
        private readonly BlueLyticsRepository $blueLyticsRepository,
        private readonly Redis $redis
    )
    {
    }

    public function getRubRates(): RubDto
    {
        $rubDto = $this->exchangeRateRepository->getRubRate();
        if ($rubDto->rubUsd === null || $rubDto->rubArs === null) {
            throw new CurrencyException("Rub rates not found!");
        }

        $this->redis->set(self::RUB_ARS, $rubDto->rubArs, self::TTL);
        $this->redis->set(self::RUB_USD, $rubDto->rubUsd, self::TTL);

        return $rubDto;
    }

    public function getUsdRates(): UsdDto
    {
        $usdDto = $this->exchangeRateRepository->getUsdRate();
        if ($usdDto->usdRub === null || $usdDto->usdArs === null) {
            throw new CurrencyException("Usd rates not found!");
        }

        $this->redis->set(self::USD_ARS, $usdDto->usdArs, self::TTL);
        $this->redis->set(self::USD_RUB, $usdDto->usdRub, self::TTL);

        return $usdDto;
    }

    public function getDollarBlueRate(): float
    {
       $blue = $this->blueLyticsRepository->getDollarBlueAvgRate();

       if ($blue !== null) {
           $this->redis->set(self::DOLLAR_BLUE, $blue, self::TTL);

           return $blue;
       }

       throw new CurrencyException("Blue val_average doesnt exists");
    }
}
