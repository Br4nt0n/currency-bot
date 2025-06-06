<?php

declare(strict_types=1);

namespace App\Infrastructure\Storage;

use App\Application\Dto\DayRateDto;
use App\Application\Storages\MongoStorageInterface;
use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Driver\CursorInterface;
use MongoException;

class MongoUsdStorage implements MongoStorageInterface
{
    private const string COLLECTION = 'USD';

    private Collection $collection;

    public function __construct(private readonly Client $client)
    {
        $this->collection = $client->selectDatabase(getenv('MONGO_DATABASE'))->selectCollection(self::COLLECTION);
    }

    public function ping(): string
    {
        $this->client->selectDatabase('admin')->command(['ping' => 1]);

        return "Pinged your deployment. You successfully connected to MongoDB with " . static::class;
    }

    public function read(): array
    {
        return $this->collection->find()->toArray();
    }

    public function find(array $filter, array $options = []): CursorInterface
    {
        return $this->collection->find($filter, $options);
    }

    public function saveDayRate(DayRateDto $rateDto): true
    {
        $result = $this->collection->insertOne($rateDto);

        if ($result->isAcknowledged() !== true) {
            $class = self::class;
            throw new MongoException("Cannot save to $class");
        }

        return true;
    }

    public function deleteMany(array $filter, array $options = []): true
    {
        $result = $this->collection->deleteMany($filter, $options);

        if ($result->isAcknowledged() !== true) {
            $class = self::class;
            throw new MongoException("Cannot delete from $class");
        }

        return true;
    }

}
