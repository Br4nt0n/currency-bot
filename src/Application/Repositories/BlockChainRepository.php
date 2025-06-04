<?php

declare(strict_types=1);

namespace App\Application\Repositories;

use App\Application\Clients\BlockchainClient;
use App\Application\Dto\BtcRubDto;
use App\Application\Dto\BtcUsdDto;
use App\Application\Exceptions\BTCException;
use Illuminate\Support\Collection;

readonly class BlockChainRepository
{
    public function __construct(private BlockchainClient $client)
    {
    }

    public function getBTCCurrency(): Collection
    {
        $result = $this->client->getBTCRate();

        if (isset($result['USD'], $result['RUB']) === false) {
            throw new BTCException("Rates not found!");
        }

        $collection = collect();
        $btcUsd = new BtcUsdDto(
            last: $result['USD']['last'] ?? null,
            buy: $result['USD']['buy'] ?? null,
            sell: $result['USD']['sell'] ?? null,
        );
        $collection->add($btcUsd);

        $btcRub = new BtcRubDto(
            last: $result['RUB']['last'] ?? null,
            buy: $result['RUB']['buy'] ?? null,
            sell: $result['RUB']['sell'] ?? null,
        );
        $collection->add($btcRub);

        return $collection;
    }

}
