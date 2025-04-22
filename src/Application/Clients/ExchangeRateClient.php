<?php

declare(strict_types=1);

namespace App\Application\Clients;

use GuzzleHttp\Client;

class ExchangeRateClient extends BaseClient
{
    private const string RUB_CODE = 'RUB';

    private const string DOLLAR_CODE = 'USD';

    private const string PESO_CODE = 'ARS';

    private string $path;

    private const string LIVE_URI = '/live?access_key=%s&source=%s&currencies=%s';

    public function __construct(private readonly Client $client, private readonly string $url, private readonly string $apiKey)
    {
        parent::__construct($client);
        $this->path = $this->url . self::LIVE_URI;
    }

    public function getLiveUsdRate(): array
    {
        $uri = sprintf($this->path,$this->apiKey, self::DOLLAR_CODE, self::RUB_CODE . ',' . self::PESO_CODE);

        return $this->sendRequest($uri);
    }

    public function getLiveRubRate(): array
    {
        $uri = sprintf($this->path,$this->apiKey, self::RUB_CODE, self::DOLLAR_CODE . ',' . self::PESO_CODE);

        return $this->sendRequest($uri);
    }
}
