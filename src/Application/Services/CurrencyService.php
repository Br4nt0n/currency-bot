<?php

declare(strict_types=1);

namespace App\Application\Services;

use Fig\Http\Message\StatusCodeInterface;
use GuzzleHttp\Client;
use HttpException;

final class CurrencyService implements CurrencyServiceInterface
{
    private const DOLLAR_BLUE_URI = 'https://api.bluelytics.com.ar/v2/latest';

    private const RUBLE_PESO_URI = '';

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

    public function getRubbleRate(): int
    {
        
    }

    public function getPesoRate(): int
    {
        // TODO: Implement getPesoRate() method.
    }

    private function sendRequest(string $uri): array
    {
        $res = $this->client->get($uri);

        if ($res->getStatusCode() !== StatusCodeInterface::STATUS_OK) {
            throw new HttpException($res->getStatusCode() . $res->getReasonPhrase());
        }

        return json_decode($res->getBody()->getContents(), true);
    }

}
