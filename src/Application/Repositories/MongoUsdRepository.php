<?php

declare(strict_types=1);

namespace App\Application\Repositories;

use App\Application\Dto\DayRateDto;
use App\Application\Enums\CurrencyPairEnum;
use App\Application\Enums\TradeDirectionEnum;
use DateTime;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Client;
use MongoDB\Collection;

class MongoUsdRepository extends AbstractMongoRepository
{
    private const string COLLECTION = 'USD';

    private Collection $collection;

    public function __construct(Client $client)
    {
        parent::__construct($client);
        $this->collection = $this->database->selectCollection(self::COLLECTION);
    }

    public function readCollection(): array
    {
        return $this->collection->find()->toArray();
    }

    public function saveDayRate(DayRateDto $rateDto): bool
    {
        $result = $this->collection->insertOne($rateDto);

        return $result->isAcknowledged();
    }

    public function getLastThirtyDays(CurrencyPairEnum $pair): array
    {
        $fromDate = new UTCDateTime((new DateTime('-30 days'))->getTimestamp() * 1000);

        $filter = [
            'date'      => ['$gte' => $fromDate],
            'pair'      => $pair->value,
            'direction' => TradeDirectionEnum::BUY->value,
        ];

        $options = [
            'sort'  => ['date' => -1], // последние сверху (опционально)
            'limit' => 50, // ограничить количество (опционально)
        ];

        $cursor = $this->collection->find($filter, $options);

        return array_map(function ($item) {
            return [
                'id' => (string)$item['_id'],
                'value' => $item['value'],
                'date' => $item['date']->toDateTime()->format('m.d'),
            ];
        }, $cursor->toArray());
    }

}
