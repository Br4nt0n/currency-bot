<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Dto\DayRateDto;
use App\Application\Repositories\MongoUsdRepository;

final class MongoDbService
{
    public function __construct(private readonly MongoUsdRepository $usdRepository)
    {
    }

    public function saveUsdRate(DayRateDto $rateDto): bool
    {
        return $this->usdRepository->saveDayRate($rateDto);
    }

    public function readCollection(): array
    {
        $collection = $this->usdRepository->readCollection();

        return $collection;
    }

}
