<?php

declare(strict_types=1);

namespace App\Application\Repositories;

use App\Application\Dto\DayRateDto;
use App\Application\Enums\CurrencyPairEnum;
use App\Application\Enums\TradeDirectionEnum;

interface MongoCurrencyInterface
{
    public function readCollection(): array;

    public function saveDayRate(DayRateDto $rateDto): bool;

    public function getLatestFor(int $days, CurrencyPairEnum $pair): array;

    public function deleteOlderThan(int $days, CurrencyPairEnum $pair, TradeDirectionEnum $direction): bool;
}
