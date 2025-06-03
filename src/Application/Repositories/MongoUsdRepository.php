<?php

declare(strict_types=1);

namespace App\Application\Repositories;

use App\Application\Dto\DayRateDto;
use App\Application\Enums\CurrencyPairEnum;
use App\Application\Enums\TradeDirectionEnum;
use App\Application\Storages\MongoStorageInterface;
use DateTime;
use MongoDB\BSON\UTCDateTime;

class MongoUsdRepository extends AbstractMongoRepository
{
    public function __construct(MongoStorageInterface $storage)
    {
        parent::__construct($storage);
    }

    public function readCollection(): array
    {
        return $this->storage->read();
    }

    public function saveDayRate(DayRateDto $rateDto): bool
    {
        return $this->storage->saveDayRate($rateDto);
    }

    public function getLatestFor(int $days, CurrencyPairEnum $pair): array
    {
        $fromDate = new UTCDateTime((new DateTime("-$days days"))->getTimestamp() * 1000);

        $filter = [
            'date'      => ['$gte' => $fromDate],
            'pair'      => $pair->value,
            'direction' => TradeDirectionEnum::BUY->value,
        ];

        $options = [
            'sort'  => ['date' => -1], // последние сверху (опционально)
            'limit' => 60, // ограничить количество (опционально)
        ];

        $cursor = $this->storage->find($filter, $options);

        return array_map(function ($item) {
            return [
                'id' => (string)$item['_id'],
                'value' => $item['value'],
                'date' => $item['date']->toDateTime()->format('m.d'),
            ];
        }, $cursor->toArray());
    }

    public function deleteOlderThan(int $days, CurrencyPairEnum $pair, TradeDirectionEnum $direction): bool
    {
        $fromDate = new UTCDateTime((new DateTime("-$days days"))->getTimestamp() * 1000);

        $filter = [
            'date'      => ['$lte' => $fromDate],
            'pair'      => $pair->value,
            'direction' => $direction->value,
        ];

        return $this->storage->deleteMany($filter);
    }

}
