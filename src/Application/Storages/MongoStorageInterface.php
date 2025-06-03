<?php

declare(strict_types=1);

namespace App\Application\Storages;

use App\Application\Dto\DayRateDto;
use MongoDB\Driver\CursorInterface;

interface MongoStorageInterface
{
    public function read(): array;

    public function find(array $filter, array $options = []): CursorInterface;

    public function saveDayRate(DayRateDto $rateDto): true;

    public function deleteMany(array $filter, array $options = []): true;

}
