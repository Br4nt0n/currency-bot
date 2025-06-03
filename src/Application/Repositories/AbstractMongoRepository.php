<?php

declare(strict_types=1);

namespace App\Application\Repositories;

use App\Application\Dto\DayRateDto;
use App\Application\Enums\CurrencyPairEnum;
use App\Application\Enums\TradeDirectionEnum;
use App\Application\Storages\MongoStorageInterface;

abstract class AbstractMongoRepository
{
    public function __construct(protected MongoStorageInterface $storage)
    {
    }

    abstract public function readCollection(): array;

    abstract public function saveDayRate(DayRateDto $rateDto): bool;

    abstract public function getLatestFor(int $days, CurrencyPairEnum $pair): array;

    abstract public function deleteOlderThan(int $days, CurrencyPairEnum $pair, TradeDirectionEnum $direction): bool;

}
