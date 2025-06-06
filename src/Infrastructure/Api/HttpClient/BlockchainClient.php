<?php

declare(strict_types=1);

namespace App\Infrastructure\Api\HttpClient;

use App\Application\Clients\BaseClient;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;

class BlockchainClient extends BaseClient
{
    public function __construct(private LoggerInterface $logger, Client $client, private readonly string $uri)
    {
        parent::__construct($logger, $client);
    }

    public function getBTCRate(): array
    {
        $result = $this->sendRequest($this->uri);

        return $result;
    }
}
