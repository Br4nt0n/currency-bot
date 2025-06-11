<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories\Http;

use App\Application\Dto\EurDto;
use App\Application\Dto\RubDto;
use App\Application\Dto\UsdDto;
use App\Infrastructure\Api\HttpClient\ExchangeRateClient;

readonly class ExchangeRateRepository
{
    public function __construct(private ExchangeRateClient $client)
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

    public function getEurRate(): EurDto
    {
        $result =  $this->client->getLiveEurRate();

        return new EurDto(
            eurRub: $result['quotes']['EURRUB'] ?? null,
            eurArs: $result['quotes']['EURARS'] ?? null
        );
    }
}
