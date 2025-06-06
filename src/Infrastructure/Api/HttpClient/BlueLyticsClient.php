<?php

declare(strict_types=1);

namespace App\Infrastructure\Api\HttpClient;

use App\Application\Clients\BaseClient;
use GuzzleHttp\Client;
use HttpException;
use Psr\Log\LoggerInterface;

class BlueLyticsClient extends BaseClient
{
    public function __construct(private LoggerInterface $logger, Client $client, private readonly string $uri)
    {
        parent::__construct($logger, $client);
    }

    public function getDollarBlueRate(): array
    {
        $result = $this->sendRequest($this->uri);

        if (!isset($result['blue'])) {
            throw new HttpException('Value `blue` does no exists');
        }

        return $result['blue'];
    }

    public function getOfficialRate(): array
    {
        $result = $this->sendRequest($this->uri);

        if (!isset($result['oficial'])) {
            throw new HttpException('Value `blue` does no exists');
        }

        return $result['oficial'];
    }

}
