<?php

declare(strict_types=1);

namespace App\Application\Actions\Currency;

use App\Application\Actions\Action;
use App\Application\Services\CurrencyServiceInterface;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Redis;

class RubleRateAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        private readonly CurrencyServiceInterface $service,
        private readonly Redis $redis
    )
    {
        parent::__construct($logger);
    }

    protected function action(): Response
    {
        $arsCacheValue = $this->redis->get(CurrencyServiceInterface::RUB_ARS);
        $usdCacheValue = $this->redis->get(CurrencyServiceInterface::RUB_USD);

        if ($arsCacheValue !== false && $usdCacheValue !== false) {
            return $this->respondWithData(['ARS' => $arsCacheValue, 'RUB' => $usdCacheValue], StatusCodeInterface::STATUS_ACCEPTED);
        }

        $rubRates = $this->service->getRubRates();

        return $this->respondWithData(['ARS' => $rubRates->rubArs, 'USD' => $rubRates->rubUsd]);
    }

}
