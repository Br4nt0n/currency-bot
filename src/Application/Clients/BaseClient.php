<?php

declare(strict_types=1);

namespace App\Application\Clients;

use Fig\Http\Message\StatusCodeInterface;
use GuzzleHttp\Client;
use HttpException;
use Psr\Log\LoggerInterface;

abstract class BaseClient
{
    public function __construct(private readonly LoggerInterface $logger, private readonly Client $client)
    {
    }

    protected function sendRequest(string $uri): array
    {
        $result = $this->client->get($uri);

        if ($result->getStatusCode() !== StatusCodeInterface::STATUS_OK) {
            $this->logger->critical($result->getStatusCode() . $result->getReasonPhrase());
            throw new HttpException($result->getStatusCode() . $result->getReasonPhrase());
        }

        return json_decode($result->getBody()->getContents(), true, JSON_THROW_ON_ERROR);
    }

}
