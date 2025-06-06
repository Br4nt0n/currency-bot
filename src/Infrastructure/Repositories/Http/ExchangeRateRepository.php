<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories\Http;

use App\Application\Dto\RubDto;
use App\Application\Dto\UsdDto;
use App\Infrastructure\Api\HttpClient\ExchangeRateClient;

class ExchangeRateRepository
{
    public function __construct(private readonly ExchangeRateClient $client)
    {
    }

    public function getUsdRate(): UsdDto
    {
        $result = $this->client->getLiveUsdRate();

        return new UsdDto(
            usdRub: $result['quotes']['USDRUB'] ?? null,
            usdArs: $result['quotes']['USDARS'] ?? null
        );
    }

    public function getRubRate(): RubDto
    {
        $result =  $this->client->getLiveRubRate();

        return new RubDto(
            rubUsd: $result['quotes']['RUBUSD'] ?? null,
            rubArs: $result['quotes']['RUBARS'] ?? null
        );
    }
}
