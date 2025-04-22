<?php

declare(strict_types=1);

namespace App\Application\Clients;

use GuzzleHttp\Client;
use HttpException;

class BlueLyticsClient extends BaseClient
{
    public function __construct(Client $client, private readonly string $uri)
    {
        parent::__construct($client);
    }

    public function getDollarBlueRate(): array
    {
        $result = $this->sendRequest($this->uri);

        if (!isset($result['blue'])) {
            throw new HttpException('Value `blue` does no exists');
        }

        return $result['blue'];
    }

}
