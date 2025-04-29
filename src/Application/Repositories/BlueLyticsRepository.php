<?php

declare(strict_types=1);

namespace App\Application\Repositories;

use App\Application\Clients\BlueLyticsClient;
use App\Application\Dto\UsdBlueDto;
use App\Application\Dto\UsdOfficialDto;

class BlueLyticsRepository
{
    public function __construct(private readonly BlueLyticsClient $client)
    {
    }

    public function getDollarBlueRates(): UsdBlueDto
    {
        $result = $this->client->getDollarBlueRate();

        return new UsdBlueDto(
            buy: $result['value_buy'] ?? null,
            sell: $result['value_sell'] ?? null,
        );
    }

    public function getDollarOfficialRates(): UsdOfficialDto
    {
        $result = $this->client->getOfficialRate();

        return new UsdOfficialDto(
          buy: $result['value_buy'] ?? null,
          sell: $result['value_sell'] ?? null,
        );
    }
}
