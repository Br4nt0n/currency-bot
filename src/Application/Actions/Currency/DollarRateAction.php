<?php

declare(strict_types=1);

namespace App\Application\Actions\Currency;

use App\Application\Actions\Action;
use App\Application\Services\CurrencyServiceInterface;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Redis;

class DollarRateAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        private readonly Redis $redis,
        private readonly CurrencyServiceInterface $service
    )
    {
        parent::__construct($logger);
    }

    protected function action(): Response
    {
        $arsCacheValue = $this->redis->get(CurrencyServiceInterface::USD_ARS);
        $rubCacheValue = $this->redis->get(CurrencyServiceInterface::USD_RUB);

        if ($arsCacheValue !== false && $rubCacheValue !== false) {
            return $this->respondWithData(['ARS' => $arsCacheValue, 'RUB' => $rubCacheValue], StatusCodeInterface::STATUS_ACCEPTED);
        }

        $usdRates = $this->service->getUsdRates();

        return $this->respondWithData(['ARS' => $usdRates->usdArs, 'RUB' => $usdRates->usdRub]);
    }

}
