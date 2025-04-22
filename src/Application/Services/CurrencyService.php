<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Dto\RubDto;
use App\Application\Dto\UsdDto;
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

    public function getRubUsdRate(): ?float
    {
        $dto = $this->getRubCurrentRate();

        if ($dto->rubUsd !== null) {
            $this->redis->set(self::RUB_USD, $dto->rubUsd, self::TTL);

            return $dto->rubUsd;
        }

        throw new \Exception();
    }

    public function getRubArsRate(): ?float
    {
        $dto = $this->getRubCurrentRate();

        if ($dto->rubArs !== null) {
            $this->redis->set(self::RUB_ARS, $dto->rubArs, self::TTL);

            return $dto->rubArs;
        }

        throw new \Exception();
    }

    public function getUsdRubRate(): ?float
    {

    }

    public function getUsdArsRate(): ?float
    {

    }

    public function getDollarBlueRate(): float
    {
       $blue = $this->blueLyticsRepository->getDollarBlueAvgRate();

       if ($blue !== null) {
           $this->redis->set(self::DOLLAR_BLUE, $blue);

           return $blue;
       }

       throw new \Exception("Blue average doesnt exists");
    }

    private function getUsdCurrentRate(): UsdDto
    {
        return $this->exchangeRateRepository->getUsdRate();
    }

    private function getRubCurrentRate(): RubDto
    {
        return $this->exchangeRateRepository->getRubRate();
    }

}
