<?php

declare(strict_types=1);

namespace App\Application\Repositories;

use App\Application\Clients\BlueLyticsClient;

class BlueLyticsRepository
{
    public function __construct(private readonly BlueLyticsClient $client)
    {
    }

    public function getDollarBlueAvgRate(): ?float
    {
        $result = $this->client->getDollarBlueRate();

        return $result['value_avg'] ?? null;
    }
}
