<?php

declare(strict_types=1);

namespace App\Application\Repositories;

use App\Application\Dto\DayRateDto;
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

}
