<?php

declare(strict_types=1);

namespace App\Infrastructure\Api\HttpClient;

use App\Application\Clients\BaseClient;
use App\Application\Enums\CurrencyCodeEnum;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;

class ExchangeRateClient extends BaseClient
{
    private const string RUB = CurrencyCodeEnum::RUB->value;

    private const string USD = CurrencyCodeEnum::USD->value;

    private const string ARS = CurrencyCodeEnum::ARS->value;

    private const string EUR = CurrencyCodeEnum::EUR->value;

    private string $path;

    private const string LIVE_URI = '/live?access_key=%s&source=%s&currencies=%s';

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly Client $client,
        private readonly string $url,
        private readonly string $apiKey
    )
    {
        parent::__construct($logger, $client);
        $this->path = $this->url . self::LIVE_URI;
    }

    public function getLiveUsdRate(): array
    {
        $uri = sprintf($this->path,$this->apiKey, self::USD, self::RUB . ',' . self::ARS);

        return $this->sendRequest($uri);
    }

    public function getLiveRubRate(): array
    {
        $uri = sprintf($this->path,$this->apiKey, self::RUB, self::USD . ',' . self::ARS);

        return $this->sendRequest($uri);
    }

    public function getLiveEurRate(): array
    {
        $uri = sprintf($this->path,$this->apiKey, self::EUR, self::RUB . ',' . self::ARS);

        return $this->sendRequest($uri);
    }
}
