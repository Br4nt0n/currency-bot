<?php

declare(strict_types=1);

namespace App\Application\Repositories;

use App\Application\Dto\DayRateDto;
use MongoDB\Client;
use MongoDB\Database;

abstract class AbstractMongoRepository
{
    protected Database $database;

    public function __construct(private Client $client)
    {
        $this->database = $this->client->selectDatabase(getenv('MONGO_DATABASE'));
    }

    public function ping(): string
    {
        $this->client->selectDatabase('admin')->command(['ping' => 1]);

        return "Pinged your deployment. You successfully connected to MongoDB with " . static::class;
    }

    abstract public function readCollection(): array;

    abstract public function saveDayRate(DayRateDto $rateDto): bool;

    abstract public function getLastThirtyDays(): array;
}
