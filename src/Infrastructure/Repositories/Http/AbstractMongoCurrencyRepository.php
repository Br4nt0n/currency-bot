<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories\Http;

use App\Application\Dto\DayRateDto;
use App\Application\Enums\CurrencyPairEnum;
use App\Application\Enums\TradeDirectionEnum;
use App\Application\Repositories\MongoCurrencyInterface;
use App\Application\Storages\MongoStorageInterface;

abstract class AbstractMongoCurrencyRepository implements MongoCurrencyInterface
{
    public function __construct(protected MongoStorageInterface $storage)
    {
    }

}
