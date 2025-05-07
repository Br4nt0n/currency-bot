<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Dto\DayRateDto;
use MongoDB\Client;
use MongoDB\Database;

final class MongoDbService
{
    private const string DATABASE = 'Currency_rates';

    private const string COLLECTION = 'USD';

    private Database $database;

    public function __construct(private readonly Client $client)
    {
        $this->database = $this->client->selectDatabase(self::DATABASE);
    }

    public function ping(): string
    {
        $this->client->selectDatabase('admin')->command(['ping' => 1]);

        return "Pinged your deployment. You successfully connected to MongoDB!\n";
    }

    public function saveUsdRate(DayRateDto $rateDto): bool
    {
        $collection = $this->database->getCollection(self::COLLECTION);
        $result = $collection->insertOne($rateDto);

        return $result->isAcknowledged();
    }

    public function readCollection(): array
    {
        $collection = $this->database->getCollection(self::COLLECTION);
        return $collection->find()->toArray();
    }

}
