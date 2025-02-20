<?php

declare(strict_types=1);

namespace App\Application\Services;

use Fig\Http\Message\StatusCodeInterface;
use GuzzleHttp\Client;
use HttpException;

final class CurrencyService implements CurrencyServiceInterface
{
    private const DOLLAR_BLUE_URI = 'https://api.bluelytics.com.ar/v2/latest';

    private const EXCHANGE_RATE_URI = 'https://api.exchangerate.host/live?access_key=%s&source=%s&currencies=%s';

    private const API_KEY = 'f4cf58ea48c26f63ced20c414f5867ec';

    private const RUB_CODE = 'RUB';

    private const DOLLAR_CODE = 'USD';

    private const PESO_CODE = 'ARS';

    public function __construct(private readonly Client $client)
    {

    }

    public function getDollarBlueRate(): int
    {
        $result = $this->sendRequest(self::DOLLAR_BLUE_URI);

        if (!isset($result['blue']['value_buy'])) {
            throw new HttpException('No value buy exists');
        }

        return (int)$result['blue']['value_buy'];
    }

    public function getRubUsdRate(): ?float
    {
        $result = $this->getLiveRubRate();

        return $result['quotes']['RUBUSD'] ?? null;
    }

    public function getRubArsRate(): ?float
    {
        $result = $this->getLiveRubRate();

        return $result['quotes']['RUBARS'] ?? null;
    }

    public function getUsdRubRate(): ?float
    {
        $result = $this->getLiveUsdRate();

        return $result['quotes']['USDRUB'] ?? null;
    }

    public function getUsdArsRate(): ?float
    {
        $result = $this->getLiveUsdRate();

        return $result['quotes']['USDARS'] ?? null;
    }

    private function getLiveUsdRate(): array
    {
        $uri = sprintf(self::EXCHANGE_RATE_URI,self::API_KEY, self::DOLLAR_CODE, self::RUB_CODE . ',' . self::PESO_CODE);

        return $this->sendRequest($uri);
    }

    private function getLiveRubRate(): array
    {
        $uri = sprintf(self::EXCHANGE_RATE_URI,self::API_KEY, self::RUB_CODE, self::DOLLAR_CODE . ',' . self::PESO_CODE);

        return $this->sendRequest($uri);
    }

    private function sendRequest(string $uri): array
    {
        $result = $this->client->get($uri);

        if ($result->getStatusCode() !== StatusCodeInterface::STATUS_OK) {
            throw new HttpException($result->getStatusCode() . $result->getReasonPhrase());
        }

        return json_decode($result->getBody()->getContents(), true, JSON_THROW_ON_ERROR);
    }

}
